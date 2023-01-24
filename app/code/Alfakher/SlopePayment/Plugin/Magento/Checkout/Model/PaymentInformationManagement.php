<?php

declare(strict_types=1);


namespace Alfakher\SlopePayment\Plugin\Magento\Checkout\Model;

use Magento\Checkout\Model\PaymentInformationManagement as BasePaymentInformationManagement;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Framework\App\ObjectManager;

class PaymentInformationManagement extends BasePaymentInformationManagement
{

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;
    
    public function __construct(
        \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        ?PaymentProcessingRateLimiterInterface $paymentRateLimiter = null
    ) {
        
        $this->billingAddressManagement = $billingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartManagement = $cartManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->paymentRateLimiter = $paymentRateLimiter
            ?? ObjectManager::getInstance()->get(PaymentProcessingRateLimiterInterface::class);
        parent::__construct($billingAddressManagement,$paymentMethodManagement,$cartManagement,$paymentDetailsFactory,$cartTotalsRepository,$paymentRateLimiter);
    }

    /**
     * @inheritdoc
     * 
     */
    public function savePaymentInformation(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        file_put_contents(BP.'/var/log/a__fact.log', str_repeat('+++', 30).PHP_EOL,FILE_APPEND);
        file_put_contents(BP.'/var/log/a__fact.log', 'from custom'.PHP_EOL,FILE_APPEND);
        $this->paymentRateLimiter->limit();
        file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            if ($billingAddress) {
            file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
            $quoteRepository = $this->getCartRepository();
            file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $quoteRepository->getActive($cartId);
            $slopeInformation = $paymentMethod->getAdditionalData('slope_information');
            $quote->setData('slope_information', serialize($slopeInformation));
            file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            
            file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            $customerId = $quote->getBillingAddress()
                ->getCustomerId();
                file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            if (!$billingAddress->getCustomerId() && $customerId) {
                file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
                //It's necessary to verify the price rules with the customer data
                $billingAddress->setCustomerId($customerId);
            }
            file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            $quote->removeAddress($quote->getBillingAddress()->getId());
            file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            $quote->setBillingAddress($billingAddress);
            file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            $quote->setDataChanges(true);
            file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            $shippingAddress = $quote->getShippingAddress();
            file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
            if ($shippingAddress && $shippingAddress->getShippingMethod()) {
                file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
                $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
                file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
                if ($shippingRate) {
                    file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
                    $shippingAddress->setLimitCarrier($shippingRate->getCarrier());
                }
            }
        }
        file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        file_put_contents(BP.'/var/log/a__fact.log', __LINE__.PHP_EOL,FILE_APPEND);
        return true;
    }

    /**
     * Get Cart repository
     *
     * @return \Magento\Quote\Api\CartRepositoryInterface
     * @deprecated 100.2.0
     */
    private function getCartRepository()
    {
        if (!$this->cartRepository) {
            $this->cartRepository = ObjectManager::getInstance()
                ->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        }
        return $this->cartRepository;
    }
}