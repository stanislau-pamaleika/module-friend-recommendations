<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\RecommendationList;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use SwiftOtter\FriendRecommendations\Model\RecommendationList;

class Identity implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        foreach ($resolvedData as $listData) {
            if (isset($listData['id'])) {
                $ids[] = RecommendationList::CACHE_TAG . '_' . $listData['id'];
            }
        }
        return $ids;
    }
}
