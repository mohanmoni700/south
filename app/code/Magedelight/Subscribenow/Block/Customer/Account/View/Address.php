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

namespace Magedelight\Subscribenow\Block\Customer\Account\View;

use Magedelight\Subscribenow\Block\Customer\Account\View;
use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Filter\Template as TemplateFilter;
use Magento\Framework\Locale\TranslatedLists;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magento\Framework\Serialize\Serializer\Json;

class Address extends View
{

    protected $_template = 'customer/account/view/address.phtml';

    /**
     * @var TranslatedLists
     */
    private $translatedLists;

    /**
     * @var TemplateFilter
     */
    private $templateFilter;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Address constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param SubscribeHelper $subscribeHelper
     * @param TimezoneInterface $timezone
     * @param ProductRepositoryInterface $productRepository
     * @param TranslatedLists $translatedLists
     * @param TemplateFilter $templateFilter
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        SubscribeHelper $subscribeHelper,
        TimezoneInterface $timezone,
        ProductRepositoryInterface $productRepository,
        Json $serialize,
        TranslatedLists $translatedLists,
        TemplateFilter $templateFilter,
        CustomerSession $customerSession,
        array $data = []
    ) {
    
        parent::__construct($context, $registry, $subscribeHelper, $timezone, $productRepository, $serialize, $data);
        $this->translatedLists = $translatedLists;
        $this->templateFilter = $templateFilter;
        $this->customerSession = $customerSession;
    }

    /**
     * @param $type
     * @return string
     * @throws \Exception
     */
    public function getCustomerAddress($type)
    {
        $address = $this->getCustomer()
                ->getAddressById($this->getSubscriptionAddressId($type))
                ->getData();

        if ($address && !empty($address['country_id'])) {
            $address['country'] = $this->translatedLists->getCountryTranslation($address['country_id']);
            $street = (is_array($address['street'])) ? $address['street'][0] : $address['street'];
            $address['street1'] = $street;
            return $this->formatAddress($address);
        }
        
        return __('N/A');
    }

    private function getSubscriptionAddressId($type)
    {
        $subscription = $this->getSubscription();
        return ($type == 'billing')?$subscription->getBillingAddressId():$subscription->getShippingAddressId();
    }

    /**
     * @param $address
     * @return string
     * @throws \Exception
     */
    private function formatAddress($address)
    {
        $template = $this->_scopeConfig->getValue('customer/address_templates/html', ScopeInterface::SCOPE_STORE);
        return $this->templateFilter->setVariables($address)->filter($template);
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
                'is_current' =>  $addressFlag,
                'data' => $address->format('oneline')
            ];
        }
        return $result;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    private function getCustomer()
    {
        return $this->customerSession->create()->getCustomer();
    }
    
    /**
     * @return bool
     */
    public function isBillingEditable($storeId = 0)
    {
        return (bool) $this->_scopeConfig->getValue('md_subscribenow/product_subscription/update_billing_address', ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * @return bool
     */
    public function isShippingEditable($storeId = 0)
    {
        return (bool) $this->_scopeConfig->getValue('md_subscribenow/product_subscription/update_shipping_address', ScopeInterface::SCOPE_STORE);
    }
}
