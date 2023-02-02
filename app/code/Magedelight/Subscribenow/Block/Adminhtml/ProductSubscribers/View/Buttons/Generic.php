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

namespace Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Buttons;

use Magento\Backend\Block\Widget\Context;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;

abstract class Generic
{
    /**
     * @var Context
     */
    public $context;
    
    /**
     * @var Magedelight\Subscribenow\Model\ProductSubscribers
     */
    private $subscriberFactory;
    
    /**
     * @var string status
     */
    private $status = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(
        Context $context,
        ProductSubscribersFactory $subscriberFactory
    ) {
    
        $this->subscriberFactory = $subscriberFactory;
        $this->context = $context;
    }

    /**
     * Return model ID
     * @return int|null
     */
    public function getModelId()
    {
        return $this->context->getRequest()->getParam('id');
    }
    
    /**
     * Return Product Subscriber Model
     * @return \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    public function getSubscription()
    {
        return $this->subscriberFactory->create()->load($this->getModelId());
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
    
    /**
     * Get Current Subscription
     * @return string
     */
    private function getStatus()
    {
        if ($this->status === null) {
            $this->status = $this->getSubscription()->getSubscriptionStatus();
        }
        return $this->status;
    }

    /**
     * General Condition to hide button
     * @return boolean
     */
    public function hideButton()
    {
        $status = $this->getStatus();

        if ($this->isEditMode() ||
            $status == ProfileStatus::CANCELED_STATUS ||
            $status == ProfileStatus::PAUSE_STATUS ||
            $status == ProfileStatus::COMPLETED_STATUS ||
            $status == ProfileStatus::SUSPENDED_STATUS ||
            $status == ProfileStatus::FAILED_STATUS
        ) {
            return true;
        }

        return false;
    }
    
    /**
     * Check Current Subscription Profile
     * Is not pending with hideButton method
     * @return boolean
     */
    public function isNotPending()
    {
        if (!$this->hideButton() &&
            $this->getStatus() != ProfileStatus::PENDING_STATUS
        ) {
            return true;
        }
        return false;
    }
    
    /**
     * Check Subscription is paused
     * @return boolean
     */
    public function isPaused()
    {
        if ($this->getStatus() == ProfileStatus::PAUSE_STATUS) {
            return true;
        }
        return false;
    }
    
    /**
     * Check Subscription is renewal
     * @return boolean
     */
    public function isRenewal()
    {
        if ($this->getStatus() == ProfileStatus::CANCELED_STATUS ||
            $this->getStatus() == ProfileStatus::COMPLETED_STATUS
        ) {
            return true;
        }
        return false;
    }
    
    /**
     * Is Edit Mode
     * @return boolean
     */
    public function isEditMode()
    {
        $editParam = $this->context->getRequest()->getParam('edit');
        return (bool) ($editParam === 'editable');
    }

    public function canCancel()
    {
        return $this->getStatus() == ProfileStatus::PENDING_STATUS
            || $this->getStatus() == ProfileStatus::ACTIVE_STATUS
            || $this->getStatus() == ProfileStatus::PAUSE_STATUS;
    }
}
