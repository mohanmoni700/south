<?php

declare(strict_types=1);

namespace Alfakher\Tabby\Plugin\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Order\OrderPayments as Subject;

class TabbyPayment
{
    const TABBY_INSTALLMENTS_METHOD_CODE = 'tabby_installments';

    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param OrderInterface $orderModel
     *
     * @return mixed|array
     */
    public function aroundGetOrderPaymentMethod(
        Subject        $subject,
        callable       $proceed,
        OrderInterface $orderModel
    ) {
        $result = $proceed($orderModel);

        try {
            if ($paymentMethod = $orderModel->getPayment()) {
                $paymentMethodCode = $paymentMethod->getMethod();
                if ($paymentMethodCode == self::TABBY_INSTALLMENTS_METHOD_CODE) {
                    foreach ($result as &$item) {
                        $item['name'] = $paymentMethod->getMethodInstance()->getTitle();
                    }
                }
            }
        } catch (\Exception $e) {
        }

        return $result;
    }
}
