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

namespace Magedelight\Subscribenow\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;
use Magedelight\Subscribenow\Model\ResourceModel\ProductAssociatedOrders\CollectionFactory as ProductAssociatedOrders;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory;
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;

class PaymentRefund implements ObserverInterface
{
    /**
     * @var ProductSubscriptionHistory
     */
    private $subscriptionHistory;
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

    public function __construct(
        ProductAssociatedOrders $profileOrders,
        ProductSubscribersFactory $productSubscribersFactory,
        SubscriptionHelper $subscriptionHelper,
        ProductSubscriptionHistory $subscriptionHistory
    ) {
        $this->subscriptionHistory = $subscriptionHistory;
        $this->profileOrders = $profileOrders;
        $this->productSubscribersFactory = $productSubscribersFactory;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->subscriptionHelper->isModuleEnable()) {
            return;
        }

        $payment = $observer->getEvent()->getPayment();
        $incrementId = $payment->getOrder()->getIncrementId();

        $collection = $this->profileOrders->create()->addFieldToFilter('order_id', ['eq' => $incrementId]);

        if ($collection->getSize()) {
            $id = $collection->getFirstItem()->getSubscriptionId();
            $subscription = $this->productSubscribersFactory->create()->load($id);
            $subscription->cancelSubscription(ProductSubscriptionHistory::HISTORY_BY_ADMIN);
        }
    }
}
