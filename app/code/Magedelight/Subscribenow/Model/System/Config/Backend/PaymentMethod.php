<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\System\Config\Backend;

class PaymentMethod implements \Magento\Framework\Option\ArrayInterface
{
    private $vaultMethod;
    private $storeManager;

    public function __construct(
        \Magento\Vault\Model\PaymentMethodList $vaultMethod,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->vaultMethod = $vaultMethod;
        $this->storeManager = $storeManager;
    }

    /**
     * Return array of payment method.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collections = $this->vaultMethod->getActiveList($storeId);

        $methods = [
            ['value' => 'cashondelivery', 'label' => __('Cash On Delivery')],
            ['value' => 'magedelight_cybersource', 'label' => __('Cybersource Payment Method')],
            ['value' => 'md_stripe_cards', 'label' => __('Stripe Payment Method')],
            ['value' => 'md_authorizecim', 'label' => __('Authorize.net CIM')],
            ['value' => 'md_firstdata', 'label' => __('Firstdata Payment Method')],
            ['value' => 'md_moneris', 'label' => __('Moneris Payment Method')],
            ['value' => 'md_monerisca', 'label' => __('Moneris Payment Method (Canada)')],
            ['value' => 'adyen_cc', 'label' => __('Adyen Payment Method')],
            ['value' => 'ops_cc', 'label' => __('Ingenico ePayments Cc')],
            ['value' => 'ops_alias', 'label' => __('Ingenico ePayments Saved Cc')],
            ['value' => 'magedelight_ewallet', 'label' => __('Magedelight EWallet')],
            ['value' => 'cybersourcesop', 'label' => __('Cybersource Silent Post')],
        ];

        foreach ($collections as $collection) {
            if ($collection->getTitle()) {
                $methods[] = ['value' => $collection->getCode(), 'label' => __($collection->getTitle())];
            }
        }

        return $methods;
    }
}
