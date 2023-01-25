<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Model\Magento\Checkout;

use Magento\Checkout\Model\PaymentInformationManagement as BasePaymentInformationManagement;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Model\Quote;

class PaymentInformationManagement extends BasePaymentInformationManagement
{

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    
    public function __construct(
        BillingAddressManagementInterface $billingAddressManagement,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartManagementInterface $cartManagement,
        PaymentDetailsFactory $paymentDetailsFactory,
        CartTotalRepositoryInterface $cartTotalsRepository,
        ?PaymentProcessingRateLimiterInterface $paymentRateLimiter = null
    ) {
        
        $this->billingAddressManagement = $billingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartManagement = $cartManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->paymentRateLimiter = $paymentRateLimiter
            ?? ObjectManager::getInstance()->get(PaymentProcessingRateLimiterInterface::class);
        parent::__construct(
            $billingAddressManagement,
            $paymentMethodManagement,
            $cartManagement,
            $paymentDetailsFactory,
            $cartTotalsRepository,
            $paymentRateLimiter
        );
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
        $this->paymentRateLimiter->limit();
        if ($billingAddress) {
            /** @var CartRepositoryInterface $quoteRepository */
            $quoteRepository = $this->getCartRepository();
            /** @var Quote $quote */
            $quote = $quoteRepository->getActive($cartId);
            $slopeInformation = $paymentMethod->getAdditionalData('slope_information');
            $quote->setData('slope_information', serialize($slopeInformation));
            
            $customerId = $quote->getBillingAddress()
            ->getCustomerId();
            if (!$billingAddress->getCustomerId() && $customerId) {
                //It's necessary to verify the price rules with the customer data
                $billingAddress->setCustomerId($customerId);
            }
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->setBillingAddress($billingAddress);
            $quote->setDataChanges(true);
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress && $shippingAddress->getShippingMethod()) {
                $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
                if ($shippingRate) {
                    $shippingAddress->setLimitCarrier($shippingRate->getCarrier());
                }
            }
        }
        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        return true;
    }

    /**
     * Get Cart repository
     *
     * @return CartRepositoryInterface
     * @deprecated 100.2.0
     */
    private function getCartRepository()
    {
        if (!$this->cartRepository) {
            $this->cartRepository = ObjectManager::getInstance()
                ->get(CartRepositoryInterface::class);
        }
        return $this->cartRepository;
    }
}
