<?php

namespace Alfakher\HandlingFee\Observer;

/**
 *
 */
use Alfakher\HandlingFee\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CaptureHandlingFee implements ObserverInterface
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
        $invoice = $observer->getEvent()->getInvoice();
        try {
            $order = $invoice->getOrder();
            $order->setHandlingFeeInvoiced($invoice->getOrder()->getHandlingFeeInvoiced() + $invoice->getHandlingFee())->save();
        } catch (\Exception $e) {
            $this->logger->info('Handling Fee Invoiced Exception : ' . $e->getMessage());
        }
    }

}
