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

use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Result\PageFactory;
use Magedelight\Subscribenow\Helper\Data;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\CollectionFactory as SubscriberFactory;
use Magento\Theme\Block\Html\Pager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Profile extends Template
{

    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    
    /**
     * SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * Data
     */
    private $helper;
   
    /**
     * PaymentHelper
     */
    private $paymentHelper;
    
    /**
     * CustomerSession
     */
    private $customerSession;

    /**
     * @var TimezoneInterface
     */
    private $timezone;
    
    
    /**
     * @var \Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\Collection
     */
    private $subscriptionOrders;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param PaymentHelper $paymentHelper
     * @param CustomerSession $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        PaymentHelper $paymentHelper,
        CustomerSession $customerSession,
        SubscriberFactory $subscriberFactory,
        TimezoneInterface $timezone,
        $data = []
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->paymentHelper = $paymentHelper;
        $this->customerSession = $customerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->timezone = $timezone;
        parent::__construct($context, $data);
    }

    /**
     * Render Template File
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        if ($this->getSubscription()) {
            /** @var \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getLayout()->createBlock(Pager::class, 'subscribenow.account.profile.pager');
            $pager->setCollection($this->getSubscription());
            $this->setChild('pager', $pager);
            $this->getSubscription()->load();
        }
        
        return $this;
    }

    /**
     * Get Current Customer ID
     * @return int
     */
    private function getCustomerId()
    {
        return $this->customerSession->create()->getCustomerId();
    }

    /**
     * Get Subscription Collection
     * @return \Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\Collection
     */
    public function getSubscription()
    {
        if (!$this->subscriptionOrders) {
            $this->subscriptionOrders = $this->subscriberFactory->create()
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->setOrder('created_at', 'desc');
        }
        return $this->subscriptionOrders;
    }

    /**
     * Previous page URL
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }

        return $this->getUrl('customer/account/');
    }

    /**
     * Return subscription status
     * @param int $statusId
     * @return string|null
     */
    public function getSubscriptionStatus($statusId)
    {
        $status = $this->helper->getStatusLabel();
        return $status[$statusId];
    }

    /**
     * Return Payment method code label
     * @param int $subscriptionId
     * @return string
     */
    public function getViewUrl($subscriptionId)
    {
        return $this->getUrl('subscribenow/account/view', ['id' => $subscriptionId]);
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    /**
     * Return next occurrence date
     * @param type $subscription
     * @return string
     */
    public function getNextOccurrenceDate($subscription)
    {
        $status = $subscription->getSubscriptionStatus();
        $date = $subscription->getNextOccurrenceDate();
        
        if (!$date || $date == '0000-00-00 00:00:00'
            || $status == ProfileStatus::COMPLETED_STATUS
            || $status == ProfileStatus::CANCELED_STATUS
            || $status == ProfileStatus::SUSPENDED_STATUS
            || $status == ProfileStatus::FAILED_STATUS
        ) {
            return '-';
        }

        return $this->formatDate($date, 1);
    }
}
