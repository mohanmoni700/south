<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Observer;

use Alfakher\SlopePayment\Model\Payment\SlopePayment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;

class SaveSlopeInformationToOrder implements ObserverInterface
{
    /**
     * Quotes repository
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Class constructor
     *
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Save slope information to order
     *
     * @param Observer $observer
     * @return void
     */
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
