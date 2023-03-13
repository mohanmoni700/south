<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Model\Payment;

use Alfakher\SlopePayment\Block\Form\SlopePayment as SlopePaymentFormType;
use Magento\Payment\Block\Info\Instructions;
use Magento\Payment\Model\Method\AbstractMethod;

class SlopePayment extends AbstractMethod
{
    public const PAYMENT_METHOD_SLOPEPAYMENT_CODE = 'slope_payment';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_SLOPEPAYMENT_CODE;

    /**
     * Slope payment block paths
     *
     * @var string
     */
    protected $_formBlockType = SlopePaymentFormType::class;

    /**
     * Info instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = Instructions::class;

    /**
     * Restrict payment method for backend order creation
     *
     * @var boolean
     */
    protected $_canUseInternal = false;

    /**
     * Availability option for refund
     *
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        $instructions = $this->getConfigData('instructions');
        return $instructions !== null ? trim($instructions) : '';
    }
}
