<?php
namespace Alfakher\PaymentMethod\Block;

/**
 * Base payment information block
 *
 * @api
 * @since 100.0.2
 */
class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Alfakher_PaymentMethod::info/default.phtml';
}
