<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Magedelight\Subscribenow\Checkout\Model;

use Magedelight\Subscribenow\Plugin\Checkout\Model\ShippingInformationManagement as Subject;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement as ParentSubject;

/**
 * ShippingInformationManagement
 */
class ShippingInformationManagement
{
    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param ParentSubject $parentSubject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array
     */
    public function aroundBeforeSaveAddressInformation(
        Subject  $subject,
        callable $proceed,
        ParentSubject $parentSubject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if ($addressInformation->getShippingAddress() && $addressInformation->getBillingAddress()) {
            return $proceed($parentSubject, $cartId, $addressInformation);
        } else {
            return [$cartId, $addressInformation];
        }
    }
}
