<?php
declare(strict_types=1);

namespace HookahShisha\Veratad\Model\Query;

use HookahShisha\Veratad\Helper\Api as apiHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\Data\OrderInterface;

class Api
{
    /**
     * @var apiHelper
     */
    protected $apiHelper;

    /**
     * @var CustomerSession $customerSession
     */
    private $customerSession;

    /**
     * Api constructor.
     *
     * @param CustomerSession $customerSession
     * @param apiHelper $apiHelper
     */
    public function __construct(
        CustomerSession $customerSession,
        ApiHelper $apiHelper
    ) {
        $this->customerSession = $customerSession;
        $this->apiHelper = $apiHelper;
    }

    /**
     * Make POST requests
     *
     * @param OrderInterface $order
     * @param string|mixed $dob
     * @return array
     */
    public function apiPost($order, $dob)
    {
        $response = [];
        $enabled = $this->getKey('veratad_settings/general/enabled');
        if ($enabled && $order) {
            $billing = $order->getBillingAddress()->getData();
            $shipping = $order->getShippingAddress()->getData();
            if ($this->isLoggedIn()) {
                //check to see if customer is excluded
                $customer_group_id = $this->customerSession->getCustomer()->getGroupId();
                $excluded = $this->apiHelper->isExcludedCustomerGroup($customer_group_id);
            } else {
                $excluded = null;
            }
            if (!$excluded) {
                $isVerified = $this->verifyVeratadAddress($billing, $shipping, $dob);
                if (!$isVerified) {
                    //fail
                    $response['response'] = __("Fail");
                } else {
                    //success
                    $response['response'] = __("Pass");
                }
            } else {
                // Verified or Excluded Customer
                $response['response'] = __("Verified or Excluded Customer");
            }
        }
        return $response;
    }

    /**
     * Check loggedin User or not
     *
     * @return bool
     */
    protected function isLoggedIn(): bool
    {
        return (bool)$this->customerSession->isLoggedIn();
    }

    /**
     * Verify the Veratad Address using API
     *
     * @param array $billing
     * @param array $shipping
     * @param string $dob
     * @return bool
     */
    protected function verifyVeratadAddress($billing, $shipping, $dob)
    {
        $result = false;
        $verification_type = $this->apiHelper->getKey('settings/general/to_verify');
        if ($verification_type === "billing") {
            //billing only post
            $billing_verified = $this->apiHelper->veratadPost($billing, $dob);
            if ($billing_verified) {
                $result = true;
            }
        } elseif ($verification_type === "shipping") {
            //shipping only post
            $shipping_verified = $this->apiHelper->veratadPost($shipping, $dob);
            if ($shipping_verified) {
                $result = true;
            }
        } elseif ($verification_type === "both") {
            //both post
            $billing_verified = $this->apiHelper->veratadPost($billing, $dob);
            $shipping_verified = $this->apiHelper->veratadPost($shipping, $dob);

            if ($billing_verified && $shipping_verified) {
                $result = true;
            }

        } elseif ($verification_type === "auto_detect") {
            //name check then decide what to post
            $nameMatch = $this->apiHelper->nameDetection($billing, $shipping);
            if ($nameMatch) {
                $billing_verified = $this->apiHelper->veratadPost($billing, $dob);
                if ($billing_verified) {
                    $result = true;
                }
            } else {
                $billing_verified = $this->apiHelper->veratadPost($billing, $dob);
                $shipping_verified = $this->apiHelper->veratadPost($shipping, $dob);
                if ($billing_verified && $shipping_verified) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}
