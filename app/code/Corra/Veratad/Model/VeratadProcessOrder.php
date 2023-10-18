<?php
declare(strict_types=1);

namespace Corra\Veratad\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\GraphQl\Model\Query\ContextInterface;

class VeratadProcessOrder
{
    /* The status for ageverification failed */
    private const ORDER_STATUS_PROCESSING_AGE_VERIFICATION_FAILED_CODE = 'age_verification_failed';

    /**
     * @var VeratadApi
     */
    protected $veratadApi;

    /**
     * @var UpdateCustomerAgeVerification
     */
    protected $updateCustomerAgeVerification;

    /**
     * @var OrderExtendedManagement
     */
    protected $orderExtendedManagement;

    /**
     * Veratad Process Order Constructor
     *
     * @param OrderExtendedManagement $orderExtendedManagement
     * @param VeratadApi $veratadApi
     * @param UpdateCustomerAgeVerification $updateCustomerAgeVerification
     */
    public function __construct(
        OrderExtendedManagement $orderExtendedManagement,
        VeratadApi $veratadApi,
        UpdateCustomerAgeVerification $updateCustomerAgeVerification
    ) {
        $this->orderExtendedManagement = $orderExtendedManagement;
        $this->veratadApi = $veratadApi;
        $this->updateCustomerAgeVerification = $updateCustomerAgeVerification;
    }

    /**
     * Check Verated API requests
     *
     * @param ContextInterface|null $context
     * @param OrderInterface $order
     * @param string $dob
     * @param boolean $isAgeVerfified
     * @return array
     */
    public function handleVeratadPlaceOrder($context, $order, $dob, $isAgeVerfified)
    {
        $response = false;
        $billingAddressVerificationStatus="";
        $shippingAddressVerificationStatus="";
        $veratadDetails="";
        $enabled = $this->veratadApi->isEnabled();
        $addressPhoneRule = $this->veratadApi->getAgeMatchRulePhone();
        if ($enabled && $order) {
            $billing = $order->getBillingAddress()->getData();
            $shipping = $order->getShippingAddress()->getData();
            //name check then decide what to post
            $nameMatch = $this->veratadApi->nameDetection($billing, $shipping);
            if ($nameMatch) {
                list($response, $billingAddressVerificationStatus, $shippingAddressVerificationStatus,
                    $veratadDetails) =
                    $this->buyerAndSellerSamePerson(
                        $isAgeVerfified,
                        $shipping,
                        $dob
                    );
            } elseif ($isAgeVerfified) {
                //Person who buy and person who receives (buyer is verified)
                //verify only the receiver address with phone rule
                $shippingVerified = $this->veratadApi->veratadPost($shipping, '', $addressPhoneRule);
                if ($shippingVerified) {
                    $response = true;
                }
                $veratadDetails = "Buyer is Verified, Shipping Address Verification (ADDR_PHONE)";
                $shippingAddressVerificationStatus = $response?"PASS":"FAIL";
            } else {
                //Person who buy and person who receives (both not verified)
                $veratadDetails = "Person who buy and person who receives (both not verified),
                Billing Address Verification (ADDR_DOB), Shipping Address Verification (ADDR_PHONE)";
                list($response, $billingAddressVerificationStatus, $shippingAddressVerificationStatus) =
                    $this->buyerAndSellerDifferentPerson($billing, $shipping, $dob, $addressPhoneRule);
            }
            if ($response && !($order->getCustomerIsGuest()) && $context &&
                $this->veratadApi->saveAgeVerificationCustomer()) {
                $this->updateCustomerAgeVerification->updateAgeVerification(
                    $response,
                    $context,
                    $dob
                );
            }
        }
        return [$response,$billingAddressVerificationStatus,$shippingAddressVerificationStatus,$veratadDetails];
    }

    /**
     * Seller and Biller is the same person.
     *
     * @param bool $isAgeVerfified
     * @param array $shipping
     * @param string $dob
     * @return array
     */
    protected function buyerAndSellerSamePerson(
        $isAgeVerfified,
        $shipping,
        $dob
    ) {
        $result = false;
        if ($isAgeVerfified) {
            //billing and shipping name same, and already age verifiied
            $result = $isAgeVerfified;
            $veratadDetail = "Billing and Shipping Name Same, Age Already Verified";
            $billingVerified = "Already Verified Customer";
            $shippingVerified = "Already Verified Customer";
        } else {
            $shippingVerified = $this->veratadApi->veratadPost($shipping, $dob);
            if ($shippingVerified) {
                $result = true;
            }
            $veratadDetail = "Billing and Shipping Name Match, Age Not Verified,
                    Shipping Address Verification (ADDR_DOB)";
            $shippingVerified = $result?"PASS":"FAIL";
            $billingVerified="";
        }
        return [$result,$billingVerified,$shippingVerified,$veratadDetail];
    }

    /**
     * Person who buy and person who receives (both not verified)
     *
     * @param array $billing
     * @param array $shipping
     * @param string $dob
     * @param string $addressPhoneRule
     * @return array
     */
    protected function buyerAndSellerDifferentPerson($billing, $shipping, $dob, $addressPhoneRule)
    {
        $result = false;
        $billingVerified = $this->veratadApi->veratadPost($billing, $dob);
        $shippingVerified = $this->veratadApi->veratadPost($shipping, $dob, $addressPhoneRule);
        if ($billingVerified && $shippingVerified) {
            $result = true;
        }
        $shippingVerifyStatus = $shippingVerified?"PASS":"FAIL";
        $billingVerifyStatus = $billingVerified?"PASS":"FAIL";
        return [$result,$billingVerifyStatus,$shippingVerifyStatus];
    }

    /**
     * Save Verated Response to OrderExtended
     *
     * @param OrderInterface $order
     * @param string $dob
     * @param boolean $ageVerificationResponse
     * @param string $billing
     * @param string $shipping
     * @param string $veratadDetail
     */
    public function saveAgeVeratedOrderInfo(
        $order,
        $dob,
        $ageVerificationResponse,
        $billing,
        $shipping,
        $veratadDetail
    ) {
        if ($this->veratadApi->saveAgeVerificationOrder()) {
            $this->orderExtendedManagement->addOrderExtendedInfo(
                $order,
                $dob,
                $ageVerificationResponse,
                $billing,
                $shipping,
                $veratadDetail
            );
        }
    }

    /**
     * Change the order status based on the Ageverification Response
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function changeOrderStatus($order)
    {
        $originalStatus = $order->getConfig()->getStatusLabel($order->getStatus());
        $newStatus = $order->getConfig()->getStatusLabel(self::ORDER_STATUS_PROCESSING_AGE_VERIFICATION_FAILED_CODE);
        $order->setState(Order::STATE_PROCESSING)
            ->setStatus(self::ORDER_STATUS_PROCESSING_AGE_VERIFICATION_FAILED_CODE);
        $message = "AgeVerificationFailed: Order Status Changed From $originalStatus to $newStatus";
        $order->addCommentToStatusHistory($message);
        return $order;
    }
}
