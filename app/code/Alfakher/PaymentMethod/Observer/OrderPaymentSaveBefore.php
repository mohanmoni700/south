<?php
namespace Alfakher\PaymentMethod\Observer;

use Alfakher\PaymentMethod\Model\AchPaymentMethod;
use Alfakher\PaymentMethod\Model\PaymentMethod;
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
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->order = $order;
        $this->quoteRepository = $quoteRepository;
        $this->_serialize = $serialize;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->_state = $state;
        $this->request = $request;
    }
    /**
     * Save custom payment method data
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $paymentOrder = $order->getPayment();
        $order = $paymentOrder->getOrder();
        $quote = $observer->getQuote();
        $paymentQuote = $quote->getPayment();
        $method = $paymentQuote->getMethodInstance()->getCode();
        if ($this->_state->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMINHTML &&
            ($method === PaymentMethod::PAYMENT_METHOD_OFFLINEPAYPAL_CODE ||
                $method === AchPaymentMethod::PAYMENT_METHOD_ACHUSPAYMENT_CODE)
        ) {
            $inputParams = $this->inputParamsResolver->resolve();
            foreach ($inputParams as $inputParam) {
                if ($inputParam instanceof \Magento\Quote\Model\Quote\Payment) {
                    $paymentData = $inputParam->getData('additional_data');
                    if (isset($paymentData['paypalemail'])) {
                        $paymentQuote->setData('paypal_email', $paymentData['paypalemail']);
                        $paymentOrder->setData('paypal_email', $paymentData['paypalemail']);
                    } elseif (isset($paymentData['accountnumber'])) {
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
        } else {
            $param = $this->request->getParam('payment');
            if ($method === PaymentMethod::PAYMENT_METHOD_OFFLINEPAYPAL_CODE) {
                if (isset($param['paypal_email'])) {
                    $paymentQuote->setData('paypal_email', $param['paypal_email']);
                    $paymentOrder->setData('paypal_email', $param['paypal_email']);
                }
            } elseif ($method === AchPaymentMethod::PAYMENT_METHOD_ACHUSPAYMENT_CODE) {
                if (isset($param['account_number'])) {
                    $paymentQuote->setData('account_number', $param['account_number']);
                    $paymentQuote->setData('bank_name', $param['bank_name']);
                    $paymentQuote->setData('routing_number', $param['routing_number']);
                    $paymentQuote->setData('address', $param['address']);

                    $paymentOrder->setData('account_number', $param['account_number']);
                    $paymentOrder->setData('bank_name', $param['bank_name']);
                    $paymentOrder->setData('routing_number', $param['routing_number']);
                    $paymentOrder->setData('address', $param['address']);
                }
            }
        }
    }
}
