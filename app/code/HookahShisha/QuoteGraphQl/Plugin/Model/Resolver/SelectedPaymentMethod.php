<?php

declare(strict_types=1);

namespace HookahShisha\QuoteGraphQl\Plugin\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\SelectedPaymentMethod as selectPaymentMethodResolver;
use Magento\Store\Model\ScopeInterface;

class SelectedPaymentMethod
{

    /**
     * [Constructor]
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * [afterResolve]
     *
     * @param selectPaymentMethodResolver $subject
     * @param mixed $result
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     * @throws LocalizedException
     * @return array
     */
    public function afterResolve(
        selectPaymentMethodResolver $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cart = $value['model'];
        $payment = $cart->getPayment();
        if (!$payment) {
            return [];
        }

        if ($payment->getMethod() && $payment->getMethod() == "vrpayecommerce_creditcard" ||
            $payment->getMethod() == "vrpayecommerce_directdebit" ||
            $payment->getMethod() == "vrpayecommerce_ccsaved" ||
            $payment->getMethod() == "vrpayecommerce_ddsaved"
        ) {
            $methodTitle = $this->scopeConfig->getValue(
                'payment/' . $payment->getMethod() . '/title',
                ScopeInterface::SCOPE_STORE
            );
        } else {
            try {
                $methodTitle = $payment->getMethodInstance()->getTitle();
            } catch (LocalizedException $e) {
                $methodTitle = '';
            }
        }
        return [
            'code' => $payment->getMethod() ?? '',
            'title' => $methodTitle,
            'purchase_order_number' => $payment->getPoNumber(),
        ];
    }
}
