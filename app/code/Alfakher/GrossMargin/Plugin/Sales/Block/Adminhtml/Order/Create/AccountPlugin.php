<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Plugin\Sales\Block\Adminhtml\Order\Create;

/**
 * @author af_bv_op
 */
use Magento\Sales\Block\Adminhtml\Order\Create\Form\Account;
use Magento\Framework\View\Element\Template;

class AccountPlugin
{

    /**
     * After To Html
     *
     * @param Account $subject
     * @param string $html
     * @return string
     */
    public function afterToHtml(Account $subject, $html)
    {
        $newBlockHtml = $subject->getLayout()->createBlock(Template::class)
            ->setPurchaseOrder($subject->getQuote()->getPurchaseOrder())
            ->setTemplate('Alfakher_GrossMargin::order/create/form/purchaseOrder.phtml')->toHtml();

        return $html . $newBlockHtml;
    }
}
