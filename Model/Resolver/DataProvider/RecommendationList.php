<?php declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;

class RecommendationList
{
    public function __construct(
        private RecommendationListRepositoryInterface $listRepository,
        private SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @param string $email
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getCustomerLists(string $email): array
    {
        $this->searchCriteriaBuilder->addFilter('email', $email);
        $lists = $this->listRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        if (empty($lists)) {
            throw new GraphQlNoSuchEntityException(__('No lists for this user'));
        }

        $listData = [];
        foreach ($lists as $list) {
            $listData[] = $this->formatListData($list);
        }

        return $listData;
    }

    private function formatListData(RecommendationListInterface $list): array
    {
        return [
            'id' => (int)$list->getId(),
            'friendName' => $list->getFriendName(),
            'title' => $list->getTitle(),
            'note' => $list->getNote()
        ];
    }
}
