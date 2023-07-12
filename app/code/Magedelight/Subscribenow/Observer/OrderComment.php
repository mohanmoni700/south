<?php

namespace Magedelight\Subscribenow\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magedelight\Subscribenow\Model\ResourceModel\ProductAssociatedOrders\CollectionFactory as ProductAssociatedOrders;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory;
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magedelight\Subscribenow\Model\ProductSubscriptionHistoryFactory;
use Magento\Store\Model\StoreManagerInterface;

class OrderComment implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var ProductAssociatedOrders
     */
    private $profileOrders;

    /**
     * @var ProductSubscribersFactory
     */
    private $productSubscribersFactory;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var ProductSubscriptionHistoryFactory
     */
    private $subscriptionHistory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    
    public function __construct(
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        ProductAssociatedOrders $profileOrders,
        ProductSubscribersFactory $productSubscribersFactory,
        SubscriptionHelper $subscriptionHelper,
        ProductSubscriptionHistoryFactory $subscriptionHistory,
        StoreManagerInterface $storeManager
    ) {
        $this->historyFactory = $historyFactory;
        $this->profileOrders = $profileOrders;
        $this->productSubscribersFactory = $productSubscribersFactory;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->subscriptionHistory = $subscriptionHistory;
        $this->storeManager = $storeManager;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {   

        if (!$this->subscriptionHelper->isModuleEnable()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();

        $orderId = $order->getEntityId();

        $collection = $this->profileOrders->create()->addFieldToFilter('order_id', ['eq' => $order->getIncrementId()]);

        if ($collection->getSize()) {

            $id = $collection->getFirstItem()->getSubscriptionId();
            $subscription = $this->productSubscribersFactory->create()->load($id);
          //  $profile_id = $subscription->getProfileId();
            $profile_id = '<a href="'.$this->storeManager->getStore()->getBaseUrl().'subscribenow/account/summary/id/'.$id.'/">'.$subscription->getProfileId().'</a>';
            $comment = __("Order has been placed from Subscription profile $profile_id.");

            if ($orderId && (!empty($comment))) {
                if ($orderId) {
                    $status = $order->getStatus();
                    $history = $this->historyFactory->create();
                    $history->setComment($comment);
                    $history->setParentId($orderId);
                    $history->setIsVisibleOnFront(1);
                    $history->setIsCustomerNotified(0);
                    $history->setEntityName('order');
                    $history->setStatus($status);
                    $history->save();
                }
            }

            $this->subscriptionHistory->create()->addSubscriptionHistory(
                $id,
                '2',
                __("First order created with #%1 for Subscription profile #%2.", $order->getIncrementId(), $profile_id)
            );
        }
        
    }
}