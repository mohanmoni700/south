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
use Magedelight\Subscribenow\Model\ResourceModel\ProductAssociatedOrders\CollectionFactory as AssociateOrders;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Directory\Model\CurrencyFactory;

class Order extends AbstractSubscription
{

    /**
     * @var AssociateOrders
     */
    private $associateOrders;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollection;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var array
     */
    private $orderCurrency = [];

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;
    
    private $relatedOrder;

    /**
     * Button constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param SubscribeHelper $subscribeHelper
     * @param AssociateOrders $associateOrders
     * @param OrderCollectionFactory $orderCollection
     * @param TimezoneInterface $timezone
     * @param PriceHelper $priceHelper
     * @param CurrencyFactory $currencyFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        SubscribeHelper $subscribeHelper,
        AssociateOrders $associateOrders,
        OrderCollectionFactory $orderCollection,
        TimezoneInterface $timezone,
        PriceHelper $priceHelper,
        CurrencyFactory $currencyFactory,
        array $data = []
    ) {
    
        parent::__construct($context, $registry, $subscribeHelper, $timezone, $data);
        $this->associateOrders = $associateOrders;
        $this->orderCollection = $orderCollection;
        $this->priceHelper = $priceHelper;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * @param $amount
     * @param $order
     * @return float|string
     */
    public function formatPrice($order)
    {
        return $this->getOrderCurrency($order->getOrderCurrencyCode())
                ->formatPrecision($order->getGrandTotal(), 2);
    }

    /**
     * @param $code
     * @return Currency|\Magento\Directory\Model\Currency
     */
    public function getOrderCurrency($code)
    {
        if (!isset($this->orderCurrency[$code])) {
            $this->orderCurrency[$code] = $this->currencyFactory->create()
                ->load($code);
        }
        return $this->orderCurrency[$code];
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
     * @return array
     */
    public function getAssociateOrder()
    {
        $collection = $this->associateOrders->create()
            ->addFieldToFilter('subscription_id', $this->getSubscriptionId());
        return ($collection->getSize() > 0)?$collection->getColumnValues('order_id'):[];
    }
    
    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getRelatedOrders()
    {
        if (!$this->relatedOrder) {
            $this->relatedOrder = $this->orderCollection->create()
                ->addFieldToFilter('increment_id', ['in' => $this->getAssociateOrder()])
                ->setOrder('created_at');
        }
        
        return $this->relatedOrder;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Subscription # %1', $this->getSubscription()->getProfileId()));
        
        if ($this->getRelatedOrders()) {
            /** @var \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class, 'subscribenow.account.order.pager');
            $pager->setCollection($this->getRelatedOrders());
            $this->setChild('pager', $pager);
            $this->getRelatedOrders()->load();
        }
        
        return $this;
    }
    
    /**
     * Return Payment method code label
     * @param int $id
     * @return string
     */
    public function getViewUrl($id)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $id]);
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
