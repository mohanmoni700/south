<?php

namespace Alfakher\OfflinePaymentRecords\Model\Config\Source;

use \Magento\Payment\Model\Config;

class Adminpayments extends \Magento\Framework\DataObject implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var $_appConfigScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    /**
     * @var $_paymentModelConfig
     */
    protected $_paymentModelConfig;

    /**
     * Construct
     *
     * @param \Magento\Payment\Model\Config\Source\Allmethods $allPaymentMethod
     */
    public function __construct(
        \Magento\Payment\Model\Config\Source\Allmethods $allPaymentMethod
    ) {
        $this->allPaymentMethod = $allPaymentMethod;
    }

    /**
     * To option array
     */
    public function toOptionArray()
    {
        return $this->allPaymentMethod->toOptionArray();
    }
}
