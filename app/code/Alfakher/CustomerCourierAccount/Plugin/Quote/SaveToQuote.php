<?php

namespace Alfakher\CustomerCourierAccount\Plugin\Quote;

/**
 *
 */
use Magento\Quote\Model\QuoteRepository;

class SaveToQuote
{

    protected $quoteRepository;

    public function __construct(
        QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$extAttributes = $addressInformation->getExtensionAttributes()) {
            $quote->setCustomerCourierName(null);
            $quote->setCustomerCourierAccount(null);
            return;
        }
        $customerCourierName = $extAttributes->getCustomerCourierName();
        $customerCourierAccount = $extAttributes->getCustomerCourierAccount();
        $quote->setCustomerCourierName($customerCourierName);
        $quote->setCustomerCourierAccount($customerCourierAccount);
    }
}
