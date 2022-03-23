<?php

namespace Alfakher\GrossMargin\Plugin\Sales\Block\Adminhtml\Order\Create;

/**
 *
 */
class AccountPlugin
{

    public function afterToHtml(\Magento\Sales\Block\Adminhtml\Order\Create\Form\Account $subject, $html)
    {
        $newBlockHtml = $subject->getLayout()->createBlock('\Magento\Framework\View\Element\Template')->setTemplate('Alfakher_GrossMargin::order/create/form/purchaseOrder.phtml')->toHtml();

        return $html . $newBlockHtml;
    }
}
