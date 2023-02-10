<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Adminhtml\Order;

class Update extends \Magento\Backend\App\Action
{
    protected $order;
    protected $resultPageFactory;
    protected $payment;
    protected $helperPayment;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Vrpayecommerce\Vrpayecommerce\Controller\Payment $payment,
        \Vrpayecommerce\Vrpayecommerce\Helper\Payment $helperPayment,
        \Magento\Sales\Model\Order $order
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helperPayment = $helperPayment;
        $this->order = $order;
        $this->payment = $payment;

        $this->statusPA = \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod::STATUS_PA;
    }

    /**
     * update a payment status
     * @return void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $this->order->load($orderId);
        $realOrderId = $this->order->getRealOrderId();

        $payment = $this->order->getPayment();
        $paymentMethod = $payment->getMethod();
        $this->payment->paymentMethod = $this->payment->createPaymentMethodObjectByPaymentMethod($paymentMethod);
        $updateStatusParameters = $this->payment->paymentMethod->getCredentials();

        $referenceId = $this->order->getPayment()->getAdditionalInformation('REFERENCE_ID');
        $statusResponse = $this->helperPayment->updateStatus($referenceId, $updateStatusParameters);

        if($statusResponse['isValid']){
            $returnCode = $statusResponse['response']['result']['code'];
            $returnMessage = $this->helperPayment->getErrorIdentifierBackend($returnCode);
            $transactionResult = $this->helperPayment->getTransactionResult($returnCode);

            if ($transactionResult == 'ACK') {
                $this->payment->order = $this->order;
                $paymentCode = $statusResponse['response']['result']['code'];
                $paymentType = $statusResponse['response']['paymentType'];

                if ($paymentType == 'PA') {
                    $this->order->getPayment()->setAdditionalInformation('ORDER_STATUS_CODE', 'PA');
                    $this->order->setState('new')->setStatus($this->statusPA)->save();
                    $this->order->addStatusToHistory($this->statusPA, '', true)->save();
                } elseif ($paymentType == 'DB') {
                    $this->order->getPayment()->setAdditionalInformation('ORDER_STATUS_CODE', 'DB');
                    $this->payment->order = $this->order;
                    $this->payment->createInvoice();
                }
                $this->payment->redirectSuccessOrderDetail('SUCCESS_GENERAL_UPDATE_PAYMENT', $orderId);
            } else {
                $this->payment->redirectErrorOrderDetail('ERROR_UPDATE_BACKEND', $orderId, $returnMessage);
            }
        } else {
            $this->payment->redirectErrorOrderDetail($statusResponse['response'], $orderId);
        }
    }

}