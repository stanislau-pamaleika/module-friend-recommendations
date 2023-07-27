<?php declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\RecommendationList;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider\RecommendationList\Product as ProductProvider;

class Product implements ResolverInterface
{
    public function __construct(
        private ProductProvider $productProvider,
        private ValueFactory $valueFactory
    ) {
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['id'])) {
            return [];
        }

        $this->productProvider->addListIdFilter($value['id']);

        return $this->valueFactory->create(function () use ($value) {
            $listProducts = $this->productProvider->getAllListProducts();
            $lists = [];
            foreach ($listProducts as $listProduct) {
                $listId = $listProduct['list_id'] ?? null;
                if ($listId == $value['id']) {
                    $lists[] = $listProduct;
                }
            }

            return $lists;
        });
    }
}
