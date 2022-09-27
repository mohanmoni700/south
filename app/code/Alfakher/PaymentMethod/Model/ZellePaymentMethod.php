<?php
namespace Alfakher\PaymentMethod\Model;

/**
 * Zelle payment method model
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class ZellePaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    public const PAYMENT_METHOD_ZELLE_CODE = 'zelle';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_ZELLE_CODE;

    /**
     * Bank Transfer payment block paths
     *
     * @var string
     */
    protected $_formBlockType = \Alfakher\PaymentMethod\Block\Form\ZellPayment::class;

    /**
     * Instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}
