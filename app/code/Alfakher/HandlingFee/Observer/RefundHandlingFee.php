<?php

namespace Alfakher\HandlingFee\Observer;

/**
 * After credit memo create
 *
 * @author af_bv_op
 */
use Alfakher\HandlingFee\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RefundHandlingFee implements ObserverInterface
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
        $creditmemo = $observer->getEvent()->getCreditmemo();
        try {
            $order = $creditmemo->getOrder();
            if ($creditmemo->getHandlingFee() > 0) {

                $order->setHandlingFeeRefunded(
                    $creditmemo->getOrder()->getHandlingFeeRefunded() + $creditmemo->getHandlingFee()
                );
            }
        } catch (\Exception $e) {
            $this->logger->info('Handling Fee Invoiced Exception : ' . $e->getMessage());
        }
    }
}
