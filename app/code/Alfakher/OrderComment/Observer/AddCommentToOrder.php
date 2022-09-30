<?php
namespace Alfakher\OrderComment\Observer;

class AddCommentToOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /** @var $order \Magento\Sales\Model\Order * */
        $order = $observer->getEvent()->getOrder();

        /** @var $quote \Magento\Quote\Model\Quote * */
        $quote = $observer->getEvent()->getQuote();

        $order->setData('order_comment', $quote->getData('order_comment'));
    }
}
