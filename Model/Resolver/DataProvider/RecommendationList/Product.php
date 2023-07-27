<?php declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider\RecommendationList;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class Product
{
    public function __construct(
        private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private ProductRepositoryInterface $productRepository,
        private ImageFactory $imageFactory
    ) {
    }

    public function getListProducts(int $listId): array
    {
        $listProducts = [];
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter('recommendation_list_ids', $listId);
        $products = $this->productRepository->getList($searchCriteriaBuilder->create())->getItems();

        foreach ($products as $product) {
            $listProducts[] = [
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'thumbnailUrl' => $this->getProductThumbnailUrl($product)
            ];
        }

        return $listProducts;
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
