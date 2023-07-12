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
namespace Magedelight\Subscribenow\Block\Customer\Account;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;

class Button extends Template
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SubscribeHelper
     */
    private $subscriptionHelper;

    /**
     * Button constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param SubscribeHelper $subscriptionHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        SubscribeHelper $subscriptionHelper,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @return \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    public function getSubscription()
    {
        return $this->registry->registry('current_profile');
    }

    /**
     * @return bool
     */
    public function canResume()
    {
        return $this->getSubscription()->getSubscriptionStatus() == ProfileStatus::PAUSE_STATUS;
    }

    /**
     * @return bool
     */
    public function canCancel()
    {
        return $this->subscriptionHelper->canCancelSubscription() &&
        $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::CANCELED_STATUS;
    }

    /**
     * @return bool
     */
    public function canSkip()
    {
        return $this->subscriptionHelper->canSkipSubscription() &&
            $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::CANCELED_STATUS &&
            $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::PENDING_STATUS &&
            $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::PAUSE_STATUS;
    }

    /**
     * @return bool
     */
    public function canPause()
    {
        return $this->subscriptionHelper->canPauseSubscription() &&
            $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::CANCELED_STATUS &&
            $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::PENDING_STATUS &&
            $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::PAUSE_STATUS;
    }

    /**
     * @return bool
     */
    public function canEdit()
    {
        return $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::CANCELED_STATUS &&
            $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::PAUSE_STATUS &&
            $this->getSubscription()->getSubscriptionStatus() != ProfileStatus::PENDING_STATUS;
    }

    /**
     * @return bool
     */
    public function canUpdateProfile()
    {
        if ($this->isEditMode()) {
            return false;
        }
        $status = $this->getSubscription()->getSubscriptionStatus();
        $nextOccurrence = $this->getSubscription()->getNextOccurrenceDate();
        return $this->subscriptionHelper->isProfileEditable($status, $nextOccurrence);
    }

    /**
     * @since 200.5.0
     * @return bool
     */
    public function canRenew()
    {
        return $this->getSubscription()->getSubscriptionStatus() == ProfileStatus::COMPLETED_STATUS ||
        $this->getSubscription()->getSubscriptionStatus() == ProfileStatus::CANCELED_STATUS ||
        $this->getSubscription()->getSubscriptionStatus() == ProfileStatus::SUSPENDED_STATUS;
    }

    /**
     * @return bool
     */
    public function isEditMode()
    {
        return (bool) $this->getRequest()->getParam('edit', false);
    }

    /**
     * @return string
     */
    public function getSkipUrl()
    {
        return $this->getUrl('*/*/skip', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getPauseUrl()
    {
        return $this->getUrl('*/*/pause', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getResumeUrl()
    {
        return $this->getUrl('*/*/resume', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('*/*/*', ['_current' => true, 'edit' => true]);
    }

    /**
     * @since 200.5.0
     * @return string
     */
    public function getRenewUrl()
    {
        return $this->getUrl('*/*/renew', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/profile/');
    }
}
