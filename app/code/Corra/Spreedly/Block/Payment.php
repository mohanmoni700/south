<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Block;

use Corra\Spreedly\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 *  Represents the payment block for the admin checkout form
 */
class Payment extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Json
     */
    private $json;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigProvider $configProvider
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->json = $json;
    }

    /**
     * Retrieves the config that should be used by the block
     *
     * @return string
     */
    public function getPaymentConfig()
    {
        $payment = $this->configProvider->getConfig()['payment'];
        $config = $payment[$this->getCode()];
        $config['code'] = $this->getCode();
        return $this->json->serialize($config);
    }

    /**
     * Returns the method code for this payment method
     *
     * @return string
     */
    public function getCode()
    {
        return ConfigProvider::CODE;
    }
}
