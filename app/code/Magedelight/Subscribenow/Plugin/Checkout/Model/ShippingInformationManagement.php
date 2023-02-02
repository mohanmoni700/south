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

namespace Magedelight\Subscribenow\Plugin\Checkout\Model;

use Magento\Quote\Model\QuoteRepository;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Magedelight\Subscribenow\Helper\Data;
use Magento\Checkout\Model\ShippingInformationManagement as CheckoutShippingInformationManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;

class ShippingInformationManagement
{

    private $quoteRepository;
    private $customerFactory;
    private $addressFactory;
    private $helper;
    private $subscriptionService;

    public function __construct(
        QuoteRepository $quoteRepository,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        Data $helper,
        SubscriptionService $subscriptionService
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->helper = $helper;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        CheckoutShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if (!$this->helper->isModuleEnable()) {
            return [$cartId, $addressInformation];
        }
        $quote = $this->quoteRepository->getActive($cartId);

        if ($this->helper->hasSubscriptionProduct($quote)) {
            if (!$addressInformation->getShippingAddress()->getCustomerAddressId()) {
                $addressInformation->getShippingAddress()->setSaveInAddressBook(1);
            }
            if (!$addressInformation->getBillingAddress()->getCustomerAddressId()) {
                $addressInformation->getBillingAddress()->setSaveInAddressBook(1);
            }
        }
        
        return [$cartId, $addressInformation];
    }
}
