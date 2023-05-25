<?php

namespace Alfakher\RequestQuote\Plugin\Sales\Block\Adminhtml\Order;

use Amasty\RequestQuote\Controller\Adminhtml\Quote\Create\FromOrder;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\OrderFactory;

/**
 * Plugin Class ViewPlugin
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
     * @var OrderFactory
     */
    private OrderFactory $orderFactory;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @param AuthorizationInterface $authorization
     * @param UrlInterface $urlBuilder
     * @param OrderFactory $orderFactory
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        AuthorizationInterface $authorization,
        UrlInterface           $urlBuilder,
        OrderFactory           $orderFactory,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->authorization = $authorization;
        $this->urlBuilder = $urlBuilder;
        $this->orderFactory = $orderFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Before plugin
     *
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
                    'label' => __('Clone as Quote'),
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
     * Get clone url
     *
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

    /**
     * Return if the order as quote
     *
     * @param int $orderId
     * @return bool
     */
    private function getQuoteByOrderId($orderId): bool
    {
        $order = $this->orderFactory->create()->load($orderId);
        $quoteId = $order->getQuoteId();
        try {
            $quote = $this->quoteRepository->get($quoteId);
            return (bool)$quote->getId();
        } catch (\Exception $e) {
            return false;
        }
    }
}
