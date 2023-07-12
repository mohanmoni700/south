<?php

namespace HookahShisha\SubscribeGraphQl\Observer;

use HookahShisha\SubscribeGraphQl\Helper\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SubscriptionFailedObserver implements ObserverInterface
{
    private Logger $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Exception $exception */
        $exception = $observer->getEvent()->getData('exception');
        if (!$exception instanceof \Exception) {
            $exception = new \Exception(__('Initialized new exception object'));
        }
        /** @var string $errorMessage */
        $errorMessage = $observer->getEvent()->getData('error_message');
        $this->logger->log('');
        $this->logger->log('');
        $this->logger->log('SUBSCRIPTION REORDER ERROR START');
        $this->logger->log($exception->getMessage());
        $this->logger->log($exception->getTraceAsString());
        $this->logger->log($errorMessage);
        $this->logger->log('SUBSCRIPTION REORDER ERROR END');
        $this->logger->log('');
        $this->logger->log('');
    }
}
