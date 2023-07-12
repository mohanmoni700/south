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
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscriptionHistory\CollectionFactory as SubscriptionHistory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magedelight\Subscribenow\Model\ResourceModel\ProductAssociatedOrders\CollectionFactory as AssociateOrders;

class History extends AbstractSubscription
{

    /**
     * @var SubscriptionHistory
     */
    private $subscriptionHistory;
    
    private $historyCollection;

    /**
     * @var array
     */
    private $modifiedByLabels = [
        "0" => 'CRON',
        "1" => 'Admin',
        "2" => 'Customer'
    ];

    /**
     * @var AssociateOrders
     */
    private $associateOrders;

    /**
     * Button constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param SubscribeHelper $subscribeHelper
     * @param SubscriptionHistory $subscriptionHistory
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        SubscribeHelper $subscribeHelper,
        SubscriptionHistory $subscriptionHistory,
        TimezoneInterface $timezone,
        AssociateOrders $associateOrders,
        array $data = []
    ) {
    
        parent::__construct($context, $registry, $subscribeHelper, $timezone, $data);
        $this->subscriptionHistory = $subscriptionHistory;
        $this->associateOrders = $associateOrders;
    }

    /**
     * @param $date
     * @return string
     */
    public function getFormattedDate($date)
    {
        return $this->formatDate($date, 1);
    }

    /**
     * @return mixed
     */
    public function getSubscriptionId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @param $modifiedBy
     * @return \Magento\Framework\Phrase
     */
    public function getModifiedLabel($modifiedBy)
    {
        return __($this->modifiedByLabels[$modifiedBy]);
    }

    /**
     * @return \Magedelight\Subscribenow\Model\ResourceModel\ProductSubscriptionHistory\Collection
     */
    public function getSubscriptionHistory()
    {
        if (!$this->historyCollection) {
            $this->historyCollection = $this->subscriptionHistory->create()
                    ->addFieldToFilter('subscription_id', $this->getSubscriptionId())
                    ->setOrder('hid', 'desc');
        }
        return $this->historyCollection;
    }

    public function getOrderId()
    {
        $collection = $this->associateOrders->create()
        ->addFieldToFilter('subscription_id', $this->getSubscriptionId());
        return ($collection->getSize() > 0)?$collection->getColumnValues('order_id'):[];
    }

    public function getProfileId()
    {
        return $this->getSubscription()->getProfileId();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Subscription # %1', $this->getSubscription()->getProfileId()));
        
        if ($this->getSubscriptionHistory()) {
            /** @var \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class, 'subscribenow.account.history.pager');
            $pager->setCollection($this->getSubscriptionHistory());
            $this->setChild('pager', $pager);
            $this->getSubscriptionHistory()->load();
        }
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/profile/');
    }
}
