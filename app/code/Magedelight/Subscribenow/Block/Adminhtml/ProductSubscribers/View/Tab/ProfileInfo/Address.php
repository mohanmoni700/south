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

namespace Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\ProfileInfo;

use Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\ProfileInfo as ParentBlock;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magedelight\Subscribenow\Helper\Data;
use Magento\Framework\Filter\Template as TemplateFilter;
use Magento\Framework\Locale\TranslatedLists;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Address extends ParentBlock
{
    
    /**
     * @var TemplateFilter
     */
    private $templateFilter;
    
    /**
     * @var TranslatedLists
     */
    private $translatedLists;
    
    /**
     * @var Customer
     */
    private $customer;

    /**
     * Constructor
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param Data $helper
     * @param TimezoneInterface $timezone
     * @param TemplateFilter $templateFilter
     * @param TranslatedLists $translatedLists
     * @param Customer $customer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        Data $helper,
        TimezoneInterface $timezone,
        Json $serialize,
        TemplateFilter $templateFilter,
        TranslatedLists $translatedLists,
        CustomerFactory $customer,
        array $data = []
    ) {
        $this->templateFilter = $templateFilter;
        $this->translatedLists = $translatedLists;
        $this->customer = $customer;
        parent::__construct($context, $registry, $productRepository, $helper, $timezone, $serialize, $data);
    }
    
    public function canShowShippingAddress()
    {
        if ($this->getSubscriptionProduct()) {
            $type = $this->getSubscriptionProduct()->getTypeId();
            if ($type == "virtual" || $type == "downloadable") {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @param $type
     * @return string
     * @throws \Exception
     */
    public function getAddress($type)
    {
        $customer = $this->getCustomer();
        $address = $customer->getAddressById($this->get($type))->getData();

        if ($address && !empty($address['country_id'])) {
            $address['country'] = $this->translatedLists->getCountryTranslation($address['country_id']);
            $street = (is_array($address['street'])) ? $address['street'][0] : $address['street'];
            $address['street1'] = $street;
            return $this->formatAddress($address);
        }

        return __('N/A');
    }

    private function get($type)
    {
        $subscription = $this->getSubscription();
        return ($type == 'billing') ? $subscription->getBillingAddressId() : $subscription->getShippingAddressId();
    }

    public function formatAddress($address)
    {
        $template = $this->helper->getCustomerAddressTemplate();
        return $this->templateFilter->setVariables($address)->filter($template);
    }
    
    private function getSubscriptionAddressId($type)
    {
        $subscription = $this->getSubscription();
        return ($type == 'billing')?$subscription->getBillingAddressId():$subscription->getShippingAddressId();
    }
    
    public function getAllAddress($type)
    {
        $result = [];
        $customerAddressId = $this->getSubscriptionAddressId($type);
        
        $addresses = $this->getCustomer()->getAddresses();
        foreach ($addresses as $address) {
            $addressFlag = false;
            if ($address->getId() == $customerAddressId) {
                $addressFlag = true;
            }
            $result[$address->getId()] = [
                'is_current' => $addressFlag,
                'data' => $address->format('oneline')
            ];
        }
        return $result;
    }

    private function getCustomer()
    {
        return $this->customer->create()->load($this->getSubscription()->getCustomerId());
    }
}
