<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */


namespace Magedelight\Subscribenow\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class InitAmount
 * @package Magedelight\Subscribenow\Model\Invoice\Total
 */
class InitAmount extends AbstractTotal
{
    /**
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $invoice->setSubscribenowInitAmount(0);
        $invoice->setBaseSubscribenowInitAmount(0);
        
        $amount = $invoice->getOrder()->getSubscribenowInitAmount();
        $invoice->setSubscribenowInitAmount($amount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
        
        $baseAmount = $invoice->getOrder()->getBaseSubscribenowInitAmount();
        $invoice->setBaseSubscribenowInitAmount($baseAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseAmount);

        return $this;
    }
}
