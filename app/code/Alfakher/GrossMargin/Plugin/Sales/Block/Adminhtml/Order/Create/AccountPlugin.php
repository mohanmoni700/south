<?php

namespace Alfakher\GrossMargin\Plugin\Sales\Block\Adminhtml\Order\Create;

/**
 * @author af_bv_op
 */
class AccountPlugin
{

    /**
     * After To Html
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Form\Account $subject
     * @param string $html
     */
    public function afterToHtml(\Magento\Sales\Block\Adminhtml\Order\Create\Form\Account $subject, $html)
    {
        $newBlockHtml = $subject->getLayout()->createBlock('\Magento\Framework\View\Element\Template')->setTemplate('Alfakher_GrossMargin::order/create/form/purchaseOrder.phtml')->toHtml();

        return $html . $newBlockHtml;
    }
}
