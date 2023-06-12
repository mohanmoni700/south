<?php

namespace Alfakher\Tabby\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Tabby\Checkout\Helper\Order as OrderHelper;

class TabbyOrderVerify implements ResolverInterface
{
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
     * @param OrderHelper $orderHelper
     * @param OrderResource $orderResource
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        OrderHelper   $orderHelper,
        OrderResource $orderResource,
        OrderFactory  $orderFactory
    ) {
        $this->orderHelper = $orderHelper;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
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

        $incrementId = $args['order_increment_id'];
        $paymentStatus = $args['payment_status'];
        $paymentId = $this->getTabbyPaymentId($incrementId);

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
                $this->orderHelper->cancelCurrentOrderByIncrementId($incrementId, $comment);
            } else {
                $this->orderHelper->cancelCurrentOrderByIncrementId($incrementId);
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('%1', $e->getMessage()),
            ];
        }

        return [
            'status' => true,
            'message' => __('Order %1 successfully verified and processed', $incrementId)
        ];
    }

    /**
     * Get tabby payment id required for payment verification
     *
     * @param  string $incrementId
     * @return string
     */
    private function getTabbyPaymentId($incrementId)
    {
        /** @var Order $order */
        $order = $this->orderFactory->create();

        $this->orderResource->load($order, $incrementId, Order::INCREMENT_ID);
        if ($payment = $order->getPayment()) {
            $additionalInfo = $payment->getAdditionalInformation();
            return $additionalInfo['checkout_id'] ?? '';
        }

        return '';
    }
}
