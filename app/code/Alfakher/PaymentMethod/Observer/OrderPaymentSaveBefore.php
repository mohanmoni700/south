<?php

namespace Alfakher\PaymentMethod\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderPaymentSaveBefore implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Construct
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver,
        \Magento\Framework\App\State $state
    ) {
        $this->order = $order;
        $this->quoteRepository = $quoteRepository;
        $this->_serialize = $serialize;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->_state = $state;
    }
    /**
     * Save custom payment method data
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $inputParams = $this->inputParamsResolver->resolve();
        if ($this->_state->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            foreach ($inputParams as $inputParam) {
                if ($inputParam instanceof \Magento\Quote\Model\Quote\Payment) {
                    $paymentData = $inputParam->getData('additional_data');
                    $paymentOrder = $order->getPayment();
                    $order = $paymentOrder->getOrder();
                    $quote = $this->quoteRepository->get($order->getQuoteId());
                    $paymentQuote = $quote->getPayment();
                    $method = $paymentQuote->getMethodInstance()->getCode();
                    if ($method == 'paypal') {
                        if (isset($paymentData['paypalemail'])) {
                            $paymentQuote->setData('paypal_email', $paymentData['paypalemail']);
                            $paymentOrder->setData('paypal_email', $paymentData['paypalemail']);
                        }

                    } elseif ($method == 'ach_us_payment') {
                        if (isset($paymentData['accountnumber'])) {
                            $paymentQuote->setData('account_number', $paymentData['accountnumber']);
                            $paymentQuote->setData('bank_name', $paymentData['bankname']);
                            $paymentQuote->setData('routing_number', $paymentData['routingnumber']);
                            $paymentQuote->setData('address', $paymentData['address']);

                            $paymentOrder->setData('account_number', $paymentData['accountnumber']);
                            $paymentOrder->setData('bank_name', $paymentData['bankname']);
                            $paymentOrder->setData('routing_number', $paymentData['routingnumber']);
                            $paymentOrder->setData('address', $paymentData['address']);
                        }
                    }
                }
            }
        }
    }
}
