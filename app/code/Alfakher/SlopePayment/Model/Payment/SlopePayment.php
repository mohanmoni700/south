<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Model\Payment;

class SlopePayment extends \Magento\Payment\Model\Method\AbstractMethod
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
    protected $_formBlockType = \Alfakher\SlopePayment\Block\Form\SlopePayment::class;

    /**
     * Info instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }

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

