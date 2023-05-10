<?php

namespace Magedelight\Subscribenow\Api;

/**
 * Subscription CRUD Interface
 * @api
 */
interface SubscribeNowRepositoryInterface
{
    /**
     * Retrieve Subscription matching the specified criteria.
     * @param int $customerId
     * @param mixed|null $postParams
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersSearchResultsInterface
     */
    public function getSubscriptionsByCustomer($customerId, $postParams = null);

    /**
     * Retrieve Subscription
     * @param int $subscriptionId
     * @param int $customerId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($subscriptionId, $customerId);
}
