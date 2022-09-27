<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace HookahShisha\Checkoutchanges\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Corra\Spreedly\Model\Ui\ConfigProvider as CorraConfig;
use ParadoxLabs\FirstData\Model\ConfigProvider as ParadoxsConfig;
use Magento\Vault\Model\CreditCardTokenFactory;
use ParadoxLabs\TokenBase\Model\CardFactory;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Customer\Model\Customer;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class SelectedPaymentMethod extends \Magento\QuoteGraphQl\Model\Resolver\SelectedPaymentMethod
{
    /**
     * @param CreditCardTokenFactory $collectionFactory
     * @param CardFactory $cardCollectionFactory
     * @param PaymentMethodListInterface $paymentMethodList
     * @param Customer $customer
     * @param LoggerInterface $logger
     */
    public function __construct(
        CreditCardTokenFactory $collectionFactory,
        CardFactory $cardCollectionFactory,
        PaymentMethodListInterface $paymentMethodList,
        Customer $customer,
        LoggerInterface $logger
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->cardCollectionFactory = $cardCollectionFactory;
        $this->paymentMethodList = $paymentMethodList;
        $this->customer = $customer;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var \Magento\Quote\Model\Quote $cart */
        $cart = $value['model'];
        
        $payment = $cart->getPayment();
        if (!$payment) {
            return [];
        }

        if ($cart->getCustomerId() && !$payment->getMethod()) {
            $customerId = $cart->getCustomerId();
            $customerData = $this->customer->load($customerId);
            $storeId = $customerData->getStoreId();
            $activePaymentMethodList = $this->paymentMethodList->getActiveList($storeId);
            
            $customerId = $cart->getCustomerId();
            $paymentmethodcode = "";
            foreach ($activePaymentMethodList as $activepayment) {
                $paymentMethodCode = $activepayment->getCode();
                if ($paymentMethodCode === CorraConfig::CODE) {
                    $collection = $this->_collectionFactory->create()
                            ->getCollection()->addFieldToFilter('customer_id', $customerId)
                            ->addFieldToFilter('is_active', 1)
                            ->addFieldToFilter('is_visible', 1);
                    if (count($collection)>0) {
                        $paymentmethodcode = CorraConfig::CC_VAULT_CODE;
                        
                    }
                } elseif ($paymentMethodCode === ParadoxsConfig::CODE) {
                    $collection = $this->cardCollectionFactory->create()
                    ->getCollection()->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('active', 1);
                    if (count($collection)>0) {
                        $paymentmethodcode = ParadoxsConfig::CODE;
                    }
                }
            }
            if ($paymentmethodcode) {
                try {
                    $payment->setMethod($paymentmethodcode);
                    $payment->save();
                } catch (Exception $e) {
                    $this->logger->err($e->getMessage());
                }
            }
            
        }

        try {
            $methodTitle = $payment->getMethodInstance()->getTitle();
        } catch (LocalizedException $e) {
            $methodTitle = '';
        }

        return [
            'code' => $payment->getMethod() ?? '',
            'title' => $methodTitle,
            'purchase_order_number' => $payment->getPoNumber(),
        ];
    }
}
