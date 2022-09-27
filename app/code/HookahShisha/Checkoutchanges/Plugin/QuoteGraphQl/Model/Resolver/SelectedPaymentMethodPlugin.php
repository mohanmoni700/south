<?php

declare(strict_types=1);

namespace HookahShisha\Checkoutchanges\Plugin\QuoteGraphQl\Model\Resolver;

use Magento\QuoteGraphQl\Model\Resolver\SelectedPaymentMethod as Subject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Vault\Model\CreditCardTokenFactory;
use ParadoxLabs\TokenBase\Model\CardFactory;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Customer\Model\Customer;
use Psr\Log\LoggerInterface;
use Corra\Spreedly\Model\Ui\ConfigProvider as CorraConfig;
use ParadoxLabs\FirstData\Model\ConfigProvider as ParadoxsConfig;

class SelectedPaymentMethodPlugin
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
     * BeforeResolve
     *
     * @param Subject $subject
     * @param Field $field
     * @param array $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     */
    public function beforeResolve(
        Subject $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $cart = $value['model'];
        $payment = $cart->getPayment();

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

        return [$field, $context, $info, $value, $args];
    }
}
