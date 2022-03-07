<?php

namespace Alfakher\HandlingFee\Block\Magento\Sales\Order\Creditmemo;

/**
 * @author af_bv_op
 */
class Totals extends \Alfakher\HandlingFee\Block\Magento\Sales\Order\Totals
{
    /**
     * @inheritdoc
     */
    public function getSource()
    {
        return $this->getCreditmemo();
    }
}
