<?php declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider\RecommendationList;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListProductInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListProductRepositoryInterface;

class Product
{
    private array $listIds = [];
    private array $listProducts;

    public function __construct(
        private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private RecommendationListProductRepositoryInterface $listProductRepository,
        private ProductRepositoryInterface $productRepository,
        private ImageFactory $imageFactory
    ) {
    }

    public function addListIdFilter(int $listId): Product
    {
        $this->listIds[] = $listId;

        return $this;
    }

    public function getAllListProducts(): array
    {
        // Check if already loaded
        if (!isset($this->listProducts)) {
            // Load list/product models
            $listProducts = $this->getFilteredListProducts();

            // Load all relevant product models
            $productSkus = [];

            foreach ($listProducts as $listProduct) {
                $productSkus[] = $listProduct->getSku();
            }

            $productSkus = array_unique($productSkus);
            $products = $this->getFilteredProducts($productSkus);

            foreach ($listProducts as $listProduct) {
                if (!isset($products[$listProduct->getSku()])) {
                    continue;
                }

                $product = $products[$listProduct->getSku()];

                $this->listProducts[] = [
                    'list_id' => $listProduct->getListId(),
                    'name' => $product->getName(),
                    'sku' => $listProduct->getSku(),
                    'thumbnailUrl' => $this->getProductThumbnailUrl($product)
                ];
            }
        }

        return $this->listProducts;
    }

    /**
     * @return RecommendationListProductInterface[]
     */
    private function getFilteredListProducts(): array
    {
        if (empty($this->listIds)) {
            return [];
        }

        $listIds = array_unique($this->listIds);
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter('list_id', $listIds, 'in');

        return $this->listProductRepository->getList($searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @param array $productSkus
     * @return ProductInterface[]
     */
    private function getFilteredProducts(array $productSkus): array
    {
        $skus = array_unique($productSkus);
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter('sku', $skus, 'in');
        $products = $this->productRepository->getList($searchCriteriaBuilder->create())->getItems();

        $productsWithSkuKeys = [];
        foreach ($products as $product) {
            $productsWithSkuKeys[$product->getSku()] = $product;
        }

        return $productsWithSkuKeys;
    }

    private function getProductThumbnailUrl($product): string
    {
        return $this->imageFactory
            ->create()
            ->init($product, 'product_thumbnail')
            ->setImageFile($product->getThumbnail())
            ->resize(300)
            ->getUrl();
    }
}
