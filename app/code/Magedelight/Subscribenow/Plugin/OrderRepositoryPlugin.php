<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magedelight\Subscribenow\Model\Subscription;

/**
 * Set Extension Attribute on Order
 */
class OrderRepositoryPlugin
{

    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;
    
    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }
    
    /**
     * Set Custom Extension Order Attribute
     * @param OrderInterface $order
     *
     * @return mixed
     */
    private function setCustomExtensionOrderAttributes($order)
    {
        $initAmount = $order->getData(Subscription::INIT_AMOUNT_FIELD_NAME);
        $trialAmount = $order->getData(Subscription::TRIAL_AMOUNT_FIELD_NAME);
        
        $orderExtensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $orderExtensionAttributes ? $orderExtensionAttributes : $this->extensionFactory->create();
        
        $extensionAttributes->setSubscribenowInitAmount($initAmount);
        $extensionAttributes->setSubscribenowTrialAmount($trialAmount);
        
        return $extensionAttributes;
    }
    
    /**
     * Add "init_amount,trial_amount" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $extensionAttributes = $this->setCustomExtensionOrderAttributes($order);
        $order->setExtensionAttributes($extensionAttributes);
        return $order;
    }

    /**
     * Add "init_amount,trial_amount" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $extensionAttributes = $this->setCustomExtensionOrderAttributes($order);
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}
