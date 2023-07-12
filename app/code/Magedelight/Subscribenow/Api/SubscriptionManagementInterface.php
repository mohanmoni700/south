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
 * Interface for subscription management
 *
 * @api
 */
interface SubscriptionManagementInterface
{
    /**
     * Skip Subscription
     *
     * @param int $subscriptionId
     * @param int $customerId
     * @param int $modifiedby
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function skip($subscriptionId, $customerId, $modifiedby = 2);

    /**
     * Pause Subscription
     *
     * @param int $subscriptionId
     * @param int $customerId
     * @param int $modifiedby
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function pause($subscriptionId, $customerId, $modifiedby = 2);

    /**
     * Resume Subscription
     *
     * @param int $subscriptionId
     * @param int $customerId
     * @param int $modifiedby
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resume($subscriptionId, $customerId, $modifiedby = 2);

    /**
     * Cancel Subscription
     *
     * @param int $subscriptionId
     * @param int $customerId
     * @param int $modifiedby
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancel($subscriptionId, $customerId, $modifiedby = 2);

    /**
     * Update Subscription
     *
     * @param int $subscriptionId
     * @param int $customerId
     * @return mixed
     */
    public function update($subscriptionId, $customerId);
}
