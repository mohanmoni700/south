<?php

namespace Alfakher\GrossMargin\Plugin\Sales\Model\AdminOrder;

/**
 * @author af_bv_op
 */
class CreatePlugin
{
    /**
     * Around Import Post Data
     *
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param callable $proceed
     * @param array $data
     */
    public function aroundImportPostData(\Magento\Sales\Model\AdminOrder\Create $subject, callable $proceed, $data)
    {
        $result = $proceed($data);

        if (isset($data['purchase_order'])) {
            $result->getQuote()->addData(['purchase_order' => $data['purchase_order']]);
        }
        return $result;
    }
}
