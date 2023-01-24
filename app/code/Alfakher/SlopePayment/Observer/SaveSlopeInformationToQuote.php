<?php

namespace Alfakher\SlopePayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;


class SaveSlopeInformationToQuote implements ObserverInterface
{
    protected $quoteRepository;

    public function __construct(QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(Observer $observer)
    {

        









       /*  $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/OBSERVER.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $event = $observer->getEvent();
        $order = $event->getOrder();
        $quoteId = $event->getQuote()->getId();
        $quote = $this->quoteRepository->get($quoteId);


        $logger->info('OrderId' . $order->getEntityId());
        $logger->info('QuoteId' . $quote->getId());
        $logger->info('Quote Payment ' . $quote->getId());
        $logger->info('Quote Payment ' . $quote->getId());
        $additionalInformation = $quote->getPayment()->getAdditionalData('slope_information'); */
        
        //use Magento\Quote\Api\Data\PaymentInterface;
        
        /* $paymentMethodCode = $order->getPayment()->getMethod();
        if ($paymentMethodCode == SlopePayment::PAYMENT_METHOD_SLOPEPAYMENT_CODE) {
            $quote = $order->getQuote();
            $slopeInformation = $quote->getData('slope_information');
            $order->setData('slope_information', $slopeInformation);
            $order->save();
        } */
    }
}
