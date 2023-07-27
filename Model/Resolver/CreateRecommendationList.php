<?php declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterfaceFactory;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterface;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListProductInterface;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListProductInterfaceFactory;
use SwiftOtter\FriendRecommendations\Api\RecommendationListProductRepositoryInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;

class CreateRecommendationList implements ResolverInterface
{
    private const REQUIRED_FIELDS = [
        'email',
        'friendName',
        'productSkus'
    ];

    public function __construct(
        private RecommendationListInterfaceFactory $listFactory,
        private RecommendationListRepositoryInterface $listRepository,
        private RecommendationListProductInterfaceFactory $listProductFactory,
        private RecommendationListProductRepositoryInterface $listProductRepository
    ) {
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     * @throws CouldNotSaveException
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        $this->checkRequiredFields($args);
        $savedList = $this->saveList($args);
        $this->addProducts($args['productSkus'], (int)$savedList->getId());

        return [
            'email' => $savedList->getEmail(),
            'friendName' => $savedList->getFriendName(),
            'title' => $savedList->getTitle(),
            'note' => $savedList->getNote()
        ];
    }

    /**
     * @param array $args
     * @return void
     * @throws GraphQlInputException
     */
    private function checkRequiredFields(array $args): void
    {
        foreach (self::REQUIRED_FIELDS as $requiredField) {
            if (!isset($args[$requiredField])) {
                throw new GraphQlInputException(__('"%1" number is required to register a callback', $requiredField));
            }
        }
    }

    /**
     * @param array $args
     * @return RecommendationListInterface
     * @throws CouldNotSaveException
     */
    private function saveList(array $args): RecommendationListInterface
    {
        /** @var RecommendationListInterface $list */
        $list = $this->listFactory->create();
        $list
            ->setEmail($args['email'])
            ->setFriendName($args['friendName'])
            ->setTitle($args['title'] ?? '')
            ->setNote($args['note'] ?? '');

        return $this->listRepository->save($list);
    }

    /**
     * @param array $productSkus
     * @param int $listId
     * @return void
     * @throws CouldNotSaveException
     */
    private function addProducts(array $productSkus, int $listId): void
    {
        foreach ($productSkus as $sku) {
            /** @var RecommendationListProductInterface $listProduct */
            $listProduct = $this->listProductFactory->create();
            $listProduct
                ->setListId($listId)
                ->setSku($sku);
            $this->listProductRepository->save($listProduct);
        }
    }
}
