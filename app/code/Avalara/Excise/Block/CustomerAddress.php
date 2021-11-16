<?php

namespace Avalara\Excise\Block;

use Avalara\Excise\Helper\Config;
use Magento\Framework\View\Element\Template\Context;

class CustomerAddress extends \Magento\Framework\View\Element\Template
{
    /**
     * Validate Billing and Shipping address path
     */
    const VALIDATE_ADDRESS_PATH = 'excise/address/validation';

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * CustomerAddress constructor
     * @param Context $context
     * @param array $data
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * @return mixed
     */
    public function isValidationEnabled()
    {
        return $this->config->isModuleEnabled($this->_storeManager->getStore())
            && $this->config->isAddressValidationActivated($this->_storeManager->getStore());
    }

    /**
     * @return mixed
     */
    public function getChoice()
    {
        return $this->config->getAllowUserToChooseAddress($this->_storeManager->getStore());
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        if ($this->getChoice()) {
            return json_encode($this->config->getAddressValidationInstructionsWithChoice(
                $this->_storeManager->getStore()
            ));
        } else {
            return json_encode($this->config->getAddressValidationInstructionsWithOutChoice(
                $this->_storeManager->getStore()
            ));
        }
    }

    /**
     * @return string
     */
    public function getErrorInstructions()
    {
        return json_encode($this->config->getAddressValidationErrorInstructions($this->_storeManager->getStore()));
    }

    /**
     * @return mixed
     */
    public function getCountriesEnabled()
    {
        return $this->config->getCountriesEnabledForAddressValidation($this->_storeManager->getStore());
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_urlBuilder->getUrl(self::VALIDATE_ADDRESS_PATH);
    }

    /**
     * @return boolean
     */
    public function isBillingValidationEnabled()
    {
        return $this->config->isModuleEnabled($this->_storeManager->getStore())
            && $this->config->isAddressValidationActivated($this->_storeManager->getStore())
            && $this->config->isBillingAddressValidationEnabled($this->_storeManager->getStore());
    }
}
