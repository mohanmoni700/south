<?php

namespace Alfakher\SlopePayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Alfakher\SlopePayment\Model\Payment\SlopePayment;
use Magento\Quote\Model\QuoteRepository;

class SaveSlopeInformationToOrder implements ObserverInterface
{
    protected $quoteRepository;

    public function __construct(QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentMethodCode = $order->getPayment()->getMethod();
        if ($paymentMethodCode == SlopePayment::PAYMENT_METHOD_SLOPEPAYMENT_CODE) {
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            $slopeInformation = $quote->getData('slope_information');
            $order->setData('slope_information', $slopeInformation);
            $order->save();
        }
        
    }
}
