<?php
namespace Alfakher\OrderComment\Plugin\Block\Adminhtml;

class SalesOrderViewInfo
{
    /**
     * Show order comment block
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Info $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View\Info $subject,
        $result
    ) {
        $commentBlock = $subject->getLayout()->getBlock('order_comments');
        if ($commentBlock !== false) {
            $commentBlock->setOrderComment($subject->getOrder()
                    ->getData('order_comment'));
            $result = $result . $commentBlock->toHtml();
        }
        return $result;
    }
}
