<?php

namespace Alfakher\HandlingFee\Controller\Adminhtml\Order;

/**
 * @author af_bv_op
 */
use Magento\Sales\Model\Order;

class Updateshippingfee extends \Magento\Backend\App\Action {

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
	 * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	 * @param \Magento\Backend\Model\Auth\Session $authSession
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Backend\Model\Auth\Session $authSession
	) {
		$this->_orderRepository = $orderRepository;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->authSession = $authSession;

		parent::__construct($context);
	}

	/**
	 * Update order shipping fee
	 *
	 * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
	 */
	public function execute() {
		$status = false;
		$post = (array) $this->getRequest()->getParams();

		if (isset($post['order_id']) && $post['order_id']) {
			try {
				$order = $this->_orderRepository->get($post['order_id']);

				if ($post['type'] == 'percent') {
					$shippingAmount = $order->getShippingAmount();
					$discountAmount = $shippingAmount * ($post['amount'] / 100);

					if ($discountAmount > $order->getShippingAmount() && $discountAmount != 0) {
						$this->messageManager->addErrorMessage(__("Maximum discount on shipping fee can’t be more than $" . $order->getShippingAmount()));
						$result = $this->_resultJsonFactory->create();
						$result->setData(['status' => false]);
						return $result;
					}

					/*af_bv_op; Start*/
					if ($order->getOriginalShippingFee() <= 0) {
						$order->setOriginalShippingFee($order->getShippingAmount());
						$order->setOriginalBaseShippingAmount($order->getBaseShippingAmount());
						$order->setOriginalShippingInclTax($order->getShippingInclTax());
						$order->setOriginalBaseShippingInclTax($order->getBaseShippingInclTax());
					}
					$order->setTotalShippingFeeDiscount($order->getTotalShippingFeeDiscount() + $discountAmount);
					/*af_bv_op; End*/

					$order->setShippingAmount($order->getShippingAmount() - $discountAmount);
					$order->setBaseShippingAmount($order->getBaseShippingAmount() - $discountAmount);
					$order->setShippingInclTax($order->getShippingInclTax() - $discountAmount);
					$order->setBaseShippingInclTax($order->getBaseShippingInclTax() - $discountAmount);
					$order->setBaseGrandTotal($order->getBaseGrandTotal() - $discountAmount);
					$order->setGrandTotal($order->getGrandTotal() - $discountAmount);

				} else {
					$shippingAmount = $order->getShippingAmount();
					$discountAmount = $post['amount'];

					if ($discountAmount > $order->getShippingAmount() && $discountAmount != 0) {
						$this->messageManager->addErrorMessage(__("Maximum discount on shipping fee can’t be more than $" . $order->getShippingAmount()));
						$result = $this->_resultJsonFactory->create();
						$result->setData(['status' => false]);
						return $result;
					}

					if ($discountAmount > 0) {
						if ($order->getOriginalShippingFee() <= 0) {
							$order->setOriginalShippingFee($order->getShippingAmount());
							$order->setOriginalBaseShippingAmount($order->getBaseShippingAmount());
							$order->setOriginalShippingInclTax($order->getShippingInclTax());
							$order->setOriginalBaseShippingInclTax($order->getBaseShippingInclTax());
						}

						$order->setShippingAmount($order->getShippingAmount() - $discountAmount);
						$order->setBaseShippingAmount($order->getBaseShippingAmount() - $discountAmount);
						$order->setShippingInclTax($order->getShippingInclTax() - $discountAmount);
						$order->setBaseShippingInclTax($order->getBaseShippingInclTax() - $discountAmount);
						$order->setBaseGrandTotal($order->getBaseGrandTotal() - $discountAmount);
						$order->setGrandTotal($order->getGrandTotal() - $discountAmount);

						$order->setTotalShippingFeeDiscount($order->getTotalShippingFeeDiscount() + $discountAmount);
					} else {
						$order->setShippingAmount($order->getOriginalShippingFee());
						$order->setBaseShippingAmount($order->getOriginalBaseShippingAmount());
						$order->setShippingInclTax($order->getOriginalShippingInclTax());
						$order->setBaseShippingInclTax($order->getOriginalBaseShippingInclTax());
						$order->setBaseGrandTotal($order->getBaseGrandTotal() + $order->getTotalShippingFeeDiscount());
						$order->setGrandTotal($order->getGrandTotal() + $order->getTotalShippingFeeDiscount());

						$order->setOriginalShippingFee(0);
						$order->setOriginalBaseShippingAmount(0);
						$order->setOriginalShippingInclTax(0);
						$order->setOriginalBaseShippingInclTax(0);
						$order->setTotalShippingFeeDiscount(0);
					}
				}

				/*af_bv_op; add order comment; Start */
				$adminUser = $this->getAdminDetail();
				$order->addStatusHistoryComment("Discount applied on shipping fee by -> \"" . $adminUser->getUsername() . "\" : " . $post['amount'] . "(" . $post['type'] . ")");
				/*af_bv_op; add order comment; End */

				$this->_orderRepository->save($order);
				$this->messageManager->addSuccessMessage(__('Shipping fee updated successfully'));
				$status = true;
			} catch (\Exception $e) {
				$message = $e->getMessage();
				if (!empty($message)) {
					$this->messageManager->addErrorMessage($message);
				}
			}
		}

		$result = $this->_resultJsonFactory->create();
		$result->setData(['status' => $status]);
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
