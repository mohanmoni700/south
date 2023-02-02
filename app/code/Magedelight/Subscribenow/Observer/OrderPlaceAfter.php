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
use Magedelight\Subscribenow\Model\Service\OrderService;
use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;

class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var SubscribeHelper
     */
    private $subscribeHelper;
    /**
     * @var SubscriptionService
     */
    private $subscriptionService;

    /**
     * @param OrderService $orderService
     * @param SubscribeHelper $subscribeHelper
     * @param SubscriptionService $subscriptionService
     */
    public function __construct(
        OrderService $orderService,
        SubscribeHelper $subscribeHelper,
        SubscriptionService $subscriptionService
    ) {
        $this->orderService = $orderService;
        $this->subscribeHelper = $subscribeHelper;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->subscribeHelper->isModuleEnable()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();

        $this->subscriptionService->create($order);
    }
}
