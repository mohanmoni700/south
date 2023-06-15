<?php

declare(strict_types = 1);

namespace Alfakher\Tabby\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Tabby\Checkout\Helper\Order as OrderHelper;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

class TabbyOrderVerify implements ResolverInterface
{
    const TABBY_INSTALLMENTS_METHOD_CODE = 'tabby_installments';

    /**
     * @var OrderHelper
     */
    private $orderHelper;
    /**
     * @var OrderResource
     */
    private $orderResource;
    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $maskedQuote;

    /**
     * @param OrderHelper $orderHelper
     * @param OrderResource $orderResource
     * @param OrderFactory $orderFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteIdToMaskedQuoteIdInterface $maskedQuote
     */
    public function __construct(
        OrderHelper                     $orderHelper,
        OrderResource                   $orderResource,
        OrderFactory                    $orderFactory,
        CartRepositoryInterface         $quoteRepository,
        QuoteIdToMaskedQuoteIdInterface $maskedQuote
    ) {
        $this->orderHelper = $orderHelper;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->quoteRepository = $quoteRepository;
        $this->maskedQuote = $maskedQuote;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        if (empty($args['order_increment_id']) || empty($args['payment_status'])) {
            return [
                'status' => false,
                'message' => __("Field order_increment_id/payment_status is required and should not be empty"),
            ];
        }

        $maskedQuoteId = '';
        $incrementId = $args['order_increment_id'];
        $paymentStatus = $args['payment_status'];
        $order = $this->getOrder($incrementId);
        $paymentId = $this->getTabbyPaymentId($order);
        $paymentMethodCode = $order->getPayment() ? $order->getPayment()->getMethod() : '';

        if ($paymentMethodCode != self::TABBY_INSTALLMENTS_METHOD_CODE) {
            return [
                'status' => false,
                'message' => __("Only tabby orders can be verified")
            ];
        }

        if (empty($paymentId)) {
            return [
                'status' => false,
                'message' => __("Order %1 doesn't contain payment information", $incrementId),
            ];
        }

        try {
            if ($paymentStatus == 'SUCCESS') {
                $this->orderHelper->authorizeOrder($incrementId, $paymentId, 'success page');
            } elseif ($paymentStatus == 'FAILED') {
                $comment = __('Payment with Tabby is failed');
                $maskedQuoteId = $this->restoreQuote($order);
                $this->orderHelper->cancelCurrentOrderByIncrementId($incrementId, $comment);
            } else {
                $maskedQuoteId = $this->restoreQuote($order);
                $this->orderHelper->cancelCurrentOrderByIncrementId($incrementId);
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('%1', $e->getMessage())
            ];
        }

        return [
            'status' => true,
            'message' => __('Order %1 successfully verified and processed', $incrementId),
            'masked_quote_id' => $maskedQuoteId
        ];
    }

    /**
     * Get tabby payment id required for payment verification
     *
     * @param Order $order
     * @return string
     */
    private function getTabbyPaymentId($order)
    {
        if ($payment = $order->getPayment()) {
            $additionalInfo = $payment->getAdditionalInformation();
            return $additionalInfo['checkout_id'] ?? '';
        }

        return '';
    }

    /**
     * Restore quote from order
     *
     * @param  Order $order
     * @return string
     */
    private function restoreQuote($order)
    {
        try {
            $quote = $this->quoteRepository->get($order->getQuoteId());
            $quote->setIsActive(1)->setReservedOrderId(null);
            $this->quoteRepository->save($quote);
            return $this->maskedQuote->execute($quote->getId());
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Get loaded order object
     *
     * @param string $incrementId
     * @return Order
     */
    private function getOrder($incrementId)
    {
        /** @var Order $order */
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $incrementId, Order::INCREMENT_ID);
        return $order;
    }
}
