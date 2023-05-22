<?php

namespace Avalara\Excise\Ui\Component\Form\Element;

use Avalara\Excise\Helper\Config;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\AbstractComponent;
use Avalara\Excise\Api\UiComponentV1Interface;

class ValidateAddress extends AbstractComponent implements UiComponentV1Interface
{
    /**
     * Component name
     */
    const NAME = 'validateButton';

    /**
     * Address Validation Backend Path.
     */
    const VALIDATE_ADDRESS_BACKEND_PATH = 'excise/address/validation';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ValidateAddress constructor
     *
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                $option['url'] = $this->urlBuilder->getUrl($option['url']);
                $options[] = $option;
            }
            $config['options'] = $options;
        }

        $store = $this->storeManager->getStore();
        $config['validationEnabled'] = $this->config->isAddressValidationActivated($store);
        $hasChoice = $this->config->getAllowUserToChooseAddress($store);
        if ($hasChoice) {
            $instructions = $this->config->getAddressValidationInstructionsWithChoice($store);
        } else {
            $instructions = $this->config->getAddressValidationInstructionsWithOutChoice($store);
        }
        $config['instructions'] =  $instructions;
        $config['errorInstructions'] =  $this->config->getAddressValidationErrorInstructions($store);
        $config['countriesEnabled'] = $this->config->getCountriesEnabledForAddressValidation($store);
        $config['baseUrl'] = $this->urlBuilder->getUrl(self::VALIDATE_ADDRESS_BACKEND_PATH);

        $this->setData('config', $config);

        parent::prepare();
    }
}
