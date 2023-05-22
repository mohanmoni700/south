<?php

namespace Avalara\Excise\Block\Adminhtml\Order\Create\Totals;

use Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals;

/**
 * Class Tax
 *
 * @package Avalara\Excise\Block\Adminhtml\Order\Create\Totals
 */
/**
 * @codeCoverageIgnore
 */
class Tax extends DefaultTotals
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Avalara_Excise::order/create/totals/tax.phtml';
}
