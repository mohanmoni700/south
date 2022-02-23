<?php

namespace Alfakher\HandlingFee\Observer;

/**
 * Recording the handling fee invoiced
 */
use Alfakher\HandlingFee\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CaptureHandlingFee implements ObserverInterface
{
    /**
     * @var $helper
     */
    protected $helper;

    /**
     * @var $logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        try {
            $order = $invoice->getOrder();
            $order->setHandlingFeeInvoiced(
                $invoice->getOrder()->getHandlingFeeInvoiced() + $invoice->getHandlingFee()
            )->save();
        } catch (\Exception $e) {
            $this->logger->info('Handling Fee Invoiced Exception : ' . $e->getMessage());
        }
    }
}
