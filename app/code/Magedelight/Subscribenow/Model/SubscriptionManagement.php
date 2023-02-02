<?php

/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package  Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model;

use Magedelight\Subscribenow\Api\SubscriptionManagementInterface;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory as SubscribeFactory;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Subscription Management
 */
class SubscriptionManagement implements SubscriptionManagementInterface
{

    private $subscriberFactory;
    /**
     * @var Request
     */
    private $request;

    public function __construct(
        SubscribeFactory $subscriberFactory,
        Request $request
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->request = $request;
    }

    public function response($status = false, $message = null)
    {
        return [['success' => $status, 'message' => $message]];
    }

    /**
     * Get Subscription Object
     * @throws NoSuchEntityException
     */
    private function getSubscription($subscriptionId, $customerId)
    {
        $model = $this->subscriberFactory->create()->load($subscriptionId);
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Requested Subscription id doesn\'t exist'));
        }
        if ($model->getCustomerId() != $customerId) {
            throw new NoSuchEntityException(__('Requested Subscription doesn\'t exist for this customer'));
        }
        return $model;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function skip($subscriptionId, $customerId, $modifiedby = 2)
    {
        $model = $this->getSubscription($subscriptionId, $customerId);
        try {
            if ($this->validate($model, 'skip')) {
                $model->skipSubscription($modifiedby);
                return $this->response(true, __('Subscription date successfully skipped'));
            } else {
                return $this->response(false, __('Profile must be in active mode'));
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        return $this->response(false, __('Error during subscription skip'));
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function pause($subscriptionId, $customerId, $modifiedby = 2)
    {
        $model = $this->getSubscription($subscriptionId, $customerId);
        try {
            if ($this->validate($model, 'pause')) {
                $model->pauseSubscription($modifiedby);
                return $this->response(true, __('Subscription successfully paused'));
            } else {
                return $this->response(false, __('Profile must be in active mode'));
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        return $this->response(false, __('Error during pause subscription'));
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function resume($subscriptionId, $customerId, $modifiedby = 2)
    {
        $model = $this->getSubscription($subscriptionId, $customerId);
        try {
            if ($this->validate($model, 'resume')) {
                $model->resumeSubscription($modifiedby);
                return $this->response(true, __('Subscription successfully resumed'));
            } else {
                return $this->response(false, __('Profile must be in paused mode'));
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        return $this->response(false, __('Error during subscription resume'));
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function cancel($subscriptionId, $customerId, $modifiedby = 2)
    {
        $model = $this->getSubscription($subscriptionId, $customerId);
        try {
            if ($this->validate($model, 'cancel')) {
                $model->cancelSubscription($modifiedby);
                return $this->response(true, __('Subscription successfully cancelled'));
            } else {
                return $this->response(false, __('Profile must be in active mode'));
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        return $this->response(false, __('Error during subscription resume'));
    }

    public function modify($subscriptionId, $customerId, $modifiedby = 2, $subscription = [])
    {
        $model = $this->getSubscription($subscriptionId, $customerId);
        try {
            if ($this->validate($model, 'update')) {
                if ($this->validateUpdateParam($subscription)) {
                    $model->updateSubscription($subscription, $modifiedby);
                    return $this->response(true, __('Subscription successfully updated'));
                } else {
                    return $this->response(false, __('Subscription data is not valid'));
                }
            } else {
                return $this->response(false, __('Profile must be in active mode'));
            }
        } catch (\Exception $ex) {
            throw $ex;
        }

        return $this->response(false, __('Error during subscription updation'));
    }

    /**
     * Validate Subscription Status
     * Before Status Changed
     *
     * @return bool
     */
    private function validate($subscription, $mode)
    {
        if ($subscription->getId() && $mode) {
            $subscriptionStatus = (int) $subscription->getSubscriptionStatus();

            if ((in_array($mode, ['skip', 'cancel', 'pause', 'update'])) &&
                ($subscriptionStatus === ProfileStatus::ACTIVE_STATUS ||
                    $subscriptionStatus === ProfileStatus::PENDING_STATUS)
            ) {
                return true;
            } elseif ('resume' === $mode && $subscriptionStatus === ProfileStatus::PAUSE_STATUS) {
                return true;
            }
        }
        return false;
    }

    private function validateUpdateParam($params = null)
    {
        if (!empty($params)) {
            if (array_key_exists('qty', $params) ||
                array_key_exists('subscription_start_date', $params) ||
                array_key_exists('md_savecard', $params) ||
                array_key_exists('md_billing_address', $params) ||
                array_key_exists('md_shipping_address', $params)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getPostParams()
    {
        $postParams = $this->request->getBodyParams();
        if (empty($postParams)) {
            $postParams = $this->request->getParams();
        }
        return $postParams;
    }

    /**
     * @inheritDoc
     */
    public function update($subscriptionId, $customerId)
    {
        $subscription = $this->getPostParams();

        return $this->modify(
            $subscriptionId,
            $customerId,
            2,
            $subscription
        );
    }
}
