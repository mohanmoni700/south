<?php

namespace Alfakher\OfflinePaymentRecords\Controller\Adminhtml\Order;

/**
 *
 */
class Deletepayment extends \Magento\Backend\App\Action {

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

	public function execute() {
		$post = $this->getRequest()->getParams();
		$responce = ["status" => 0, "msg" => ""];

		try {
			$model = $this->_paymentRecords->create()->load($post['recordId']);
			$order = $this->_orderRepository->get($model->getOrderId());

			$adminUser = $this->getAdminDetail();
			$order->addStatusHistoryComment("offline payment deleted by -> \"" . $adminUser->getUsername() . "\" : " . $model->getPaymentType() . " => " . $model->getAmountPaid());

			$model->delete();
			$this->_orderRepository->save($order);

			$this->_afHelper->sendMail($order);

			$responce = ["status" => 1, "msg" => "payment deleted successfully."];
			$this->messageManager->addSuccessMessage(__('payment deleted successfully.'));

		} catch (\Exceptio $e) {
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
	public function getAdminDetail() {
		return $this->authSession->getUser();
	}
}