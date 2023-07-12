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
use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Link extends Template
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
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Button constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param SubscribeHelper $subscriptionHelper
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        SubscribeHelper $subscriptionHelper,
        TimezoneInterface $timezone,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->timezone = $timezone;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return [
            'summary' => [
                'title' => 'Summary',
                'url' => $this->getSummaryUrl()
            ],
            'product' => [
                'title' => 'Product',
                'url' => $this->getProductUrl()
            ],
            'payment' => [
                'title' => 'Payments',
                'url' => $this->getPaymentUrl()
            ],
            'address' => [
                'title' => 'Addresses',
                'url' => $this->getAddressUrl()
            ],
            'order' => [
                'title' => 'Past Orders',
                'url' => $this->getRelatedOrderUrl(),
            ],
            'history' => [
                'title' => 'Change Logs',
                'url' => $this->getHistoryUrl()
            ],
        ];
    }

    /**
     * @param $page
     * @return bool
     */
    public function isCurrentPage($page)
    {
        $actionName = $this->getRequest()->getActionName();
        return $page == $actionName;
    }

    /**
     * @return string
     */
    public function getSummaryUrl()
    {
        if ($this->isEditMode()):
            return $this->getUrl('*/*/summary/edit/1/', ['id' => $this->getSubscriptionId()]);
        else:
            return $this->getUrl('*/*/summary', ['id' => $this->getSubscriptionId()]);
        endif;
    }

    /**
     * @return string
     */
    public function getProductUrl()
    {
        if ($this->isEditMode()):
            return $this->getUrl('*/*/product/edit/1/', ['id' => $this->getSubscriptionId()]);
        else:
        return $this->getUrl('*/*/product', ['id' => $this->getSubscriptionId()]);
        endif;
    }

    /**
     * @return string
     */
    public function getPaymentUrl()
    {
        if ($this->isEditMode()):
            return $this->getUrl('*/*/payment/edit/1/', ['id' => $this->getSubscriptionId()]);
        else:
        return $this->getUrl('*/*/payment', ['id' => $this->getSubscriptionId()]);
        endif;
    }

    /**
     * @return string
     */
    public function getAddressUrl()
    {  
        if ($this->isEditMode()):
            return $this->getUrl('*/*/address/edit/1/', ['id' => $this->getSubscriptionId()]);
        else:
        return $this->getUrl('*/*/address', ['id' => $this->getSubscriptionId()]);
        endif;
    }
    
    /**
     * @return string
     */
    public function getRelatedOrderUrl()
    {
        if ($this->isEditMode()):
            return $this->getUrl('*/*/order/edit/1/', ['id' => $this->getSubscriptionId()]);
        else:
        return $this->getUrl('*/*/order', ['id' => $this->getSubscriptionId()]);
        endif;
    }

    /**
     * @return string
     */
    public function getHistoryUrl()
    {
        if ($this->isEditMode()):
            return $this->getUrl('*/*/history/edit/1/', ['id' => $this->getSubscriptionId()]);
        else:
        return $this->getUrl('*/*/history', ['id' => $this->getSubscriptionId()]);
        endif;
    }

    /**
     * @return \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    public function getSubscriptionId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return bool
     */
    public function isEditMode()
    {
        return (bool) $this->getRequest()->getParam('edit', false);
    }
}
