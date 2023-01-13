<?php

declare(strict_types=1);

namespace HookahShisha\QuoteGraphQl\Model\Resolver;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods as AvailablePaymentMethodsResolver;

class AvailablePaymentMethods extends AvailablePaymentMethodsResolver
{
    /**
     * @var PaymentInformationManagementInterface
     */
    private $informationManagement;

    /**
     * @param PaymentInformationManagementInterface $informationManagement
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PaymentInformationManagementInterface $informationManagement,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->informationManagement = $informationManagement;
        $this->scopeConfig=$scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $cart = $value['model'];
        return $this->getPaymentMethodsData($cart);
    }

    /**
     * Collect and return information about available payment methods
     *
     * @param CartInterface $cart
     * @return array
     */
    private function getPaymentMethodsData(CartInterface $cart): array
    {
        $paymentInformation = $this->informationManagement->getPaymentInformation($cart->getId());
        $paymentMethods = $paymentInformation->getPaymentMethods();

        $paymentMethodsData = [];
        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod->getCode() == "vrpayecommerce_creditcard" ||
                $paymentMethod->getCode()=="vrpayecommerce_directdebit") {
                $valueFromConfig = $this->scopeConfig->getValue(
                    'payment/'.$paymentMethod->getCode().'/title',
                    ScopeInterface::SCOPE_STORE
                );
                if ($valueFromConfig) {
                    $paymentMethodsData[] = [
                    'title' => $valueFromConfig,
                    'code' => $paymentMethod->getCode(),
                    ];
                } else {
                    $paymentMethodsData[] = [
                    'title' => $paymentMethod->getTitle(),
                    'code' => $paymentMethod->getCode(),
                    ];
                }
            } else {
                $paymentMethodsData[] = [
                'title' => $paymentMethod->getTitle(),
                'code' => $paymentMethod->getCode(),
                ];
            }
        }
        return $paymentMethodsData;
    }
}
