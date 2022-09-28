<?php
namespace Alfakher\PaymentMethod\Plugin;

class RestrictPayment
{
    /**
     * Add offline paypal on restrictd payment list
     *
     * @param \Signifyd\Connect\Helper\ConfigHelper $subject
     * @param array $result
     * @return array
     */
    public function afterGetRestrictedPaymentMethodsConfig(\Signifyd\Connect\Helper\ConfigHelper $subject, $result)
    {
        array_push($result, 'offline_paypal');
        return $result;
    }
}
