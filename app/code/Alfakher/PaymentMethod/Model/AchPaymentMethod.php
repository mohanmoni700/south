<?php
namespace Alfakher\PaymentMethod\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Ach Us Payment
 *
 * Update additional payments fields and validate the payment data
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class AchPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    public const PAYMENT_METHOD_ACHUSPAYMENT_CODE = 'ach_us_payment';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_ACHUSPAYMENT_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = \Alfakher\PaymentMethod\Block\Form\AchUsPayment::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Alfakher\PaymentMethod\Block\Info\AchUsPayment::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->getInfoInstance()->setAccountNumber($data->getAccountNumber());
        $this->getInfoInstance()->setBankName($data->getBankName());
        $this->getInfoInstance()->setRoutingNumber($data->getRoutingNumber());
        $this->getInfoInstance()->setAddress($data->getAddress());
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws LocalizedException
     * @api
     * @since 100.2.3
     */
    public function validate()
    {
        parent::validate();

        return $this;
    }
}
