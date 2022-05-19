<?php

namespace Alfakher\OfflinePaymentRecords\Controller\Adminhtml\Order;

class Addpayment extends \Magento\Backend\App\Action
{
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecordFactory $paymentRecords
     * @param \Alfakher\OfflinePaymentRecords\Helper\Data $afHelper
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecordFactory $paymentRecords,
        \Alfakher\OfflinePaymentRecords\Helper\Data $afHelper,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_paymentRecords = $paymentRecords;
        $this->authSession = $authSession;
        $this->_afHelper = $afHelper;

        parent::__construct($context);
    }

    /**
     * Execute method
     */
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $responce = ["status" => 0, "msg" => ""];

        try {
            $order = $this->_orderRepository->get($post['order_id']);

            $model = $this->_paymentRecords->create();
            $model->setData([
                'order_id' => $post['order_id'],
                'payment_type' => $post['paymentType'],
                'transaction_number' => $post['transactionNumber'],
                'amount_paid' => $post['amountPaid'],
                'transaction_date' => $post['paymentDate'],
            ]);
            $model->save();

            $adminUser = $this->getAdminDetail();

            $order->setOfflinePaymentType($post['paymentType']);
            $order->setOfflineTransactionDate($post['paymentDate']);

            $order->addStatusHistoryComment("offline payment added by -> \"" . $adminUser->getUsername() . "\" : " . $post['paymentType'] . " => " . $post['amountPaid']);
            $this->_orderRepository->save($order);

            $this->_afHelper->sendMail($order);

            $responce = ["status" => 1, "msg" => "payment added successfully."];
            $this->messageManager->addSuccessMessage(__('payment added successfully.'));
        } catch (\Exception $e) {
            $responce = ["status" => 0, "msg" => $e->getMessage()];
            $this->messageManager->addErrorMessage($message);
        }

        $result = $this->_resultJsonFactory->create();
        $result->setData($responce);
        return $result;
    }

    /**
     * Get admin user detail
     *
     * @return mixed
     */
    public function getAdminDetail()
    {
        return $this->authSession->getUser();
    }
}
