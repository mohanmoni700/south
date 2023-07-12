<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Api;

/**
 * Subscription CRUD Interface
 * @api
 */
interface ProductSubscribersRepositoryInterface
{
    /**
     * Save Subscription
     * @param \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface $productSubscribers
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface $productSubscribers
    );

    /**
     * Retrieve Subscription
     * @param int $subscriptionId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($subscriptionId);

    /**
     * Retrieve Subscription matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Subscription
     * @param \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface $productSubscribers
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface $productSubscribers
    );

    /**
     * Delete Subscription by ID
     * @param int $subscriptionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($subscriptionId);

    /**
     * @param int $orderId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createByOrderId($orderId);
}
