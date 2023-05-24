<?php

namespace Alfakher\RequestQuote\Plugin\Sales\Block\Adminhtml\Order;

use Amasty\RequestQuote\Controller\Adminhtml\Quote\Create\FromOrder;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Amasty\RequestQuote\Model\QuoteFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Amasty\RequestQuote\Model;


/**
 * Class ViewPlugin
 */
class ViewPlugin
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quoteFactory;

    public function __construct(
        AuthorizationInterface    $authorization,
        UrlInterface              $urlBuilder,
        QuoteFactory              $quoteFactory,
        OrderRepositoryInterface  $orderRepository
    )
    {
        $this->authorization = $authorization;
        $this->urlBuilder = $urlBuilder;
        $this->quoteFactory = $quoteFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param View $subject
     * @param LayoutInterface $layout
     *
     * @return array
     */
    public function beforeSetLayout(View $subject, LayoutInterface $layout)
    {
        if ($this->getQuoteByOrderId($subject->getOrderId())) {
            if ($this->authorization->isAllowed(FromOrder::ADMIN_RESOURCE)) {
                $subject->addButton('clone_as_quote', [
                    'label' => __('Clone as Quotes dfgtnju'),
                    'class' => 'clone',
                    'id' => 'clone-as-quote',
                    'onclick' => 'setLocation(\'' . $this->getCloneUrl($subject->getOrderId()) . '\')'
                ]);
            }
       } else {
            $subject->removeButton('clone_as_quote');
                }
        return [$layout];
    }

    /**
     * @param int $orderId
     * @return string
     */
    private function getCloneUrl($orderId)
    {
        return $this->urlBuilder->getUrl(
            'amasty_quote/quote_create/fromOrder',
            ['order_id' => $orderId]
        );
    }

    private function getQuoteByOrderId($orderId): bool
    {
        $quote = $this->quoteFactory->create()->load($orderId, 'reserved_order_id');
        return (bool)$quote->getId();
    }
}
