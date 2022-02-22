<?php

namespace Alfakher\HandlingFee\Observer;

/**
 *
 */
use Alfakher\HandlingFee\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RefundHandlingFee implements ObserverInterface
{
    protected $helper;
    protected $logger;

    public function __construct(
        Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        try {
            $order = $creditmemo->getOrder();
            if ($creditmemo->getHandlingFee() > 0) {

                $order->setHandlingFeeRefunded($creditmemo->getOrder()->getHandlingFeeRefunded() + $creditmemo->getHandlingFee());
            }
        } catch (\Exception $e) {
            $this->logger->info('Handling Fee Invoiced Exception : ' . $e->getMessage());
        }
    }

}
