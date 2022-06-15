<?php

namespace Alfakher\CustomerCourierAccount\Plugin\Quote;

use Magento\Quote\Model\QuoteRepository;

class SaveToQuote
{
    /**
     * @var $quoteRepository
     */
    protected $quoteRepository;

    /**
     * Construct
     *
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Before save address information
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
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
