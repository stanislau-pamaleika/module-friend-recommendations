<?php declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class RecommendationList implements ResolverInterface
{
    public function __construct(
        private GetCustomer $getCustomer,
        private DataProvider\RecommendationList $recommendationList
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(
                __('The current customer isn\'t authorized try again with authorization token')
            );
        }

        $customerEmail = $this->getCustomer->execute($context)->getEmail();

        return $this->recommendationList->getCustomerLists($customerEmail);
    }
}
