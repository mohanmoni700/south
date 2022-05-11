<?php

namespace Alfakher\OfflinePaymentRecords\Helper;

/**
 *
 */
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	public function __construct(
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Psr\Log\LoggerInterface $loggerInterface,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecordFactory $paymentRecords,
		array $data = []
	) {
		$this->_inlineTranslation = $inlineTranslation;
		$this->_transportBuilder = $transportBuilder;
		$this->_scopeConfig = $scopeConfig;
		$this->_logLoggerInterface = $loggerInterface;
		$this->storeManager = $storeManager;
		$this->_paymentRecords = $paymentRecords;
	}

	public function sendMail($order) {

		// echo "<pre>";
		// print_r(get_class_methods($order));die;

		try {

			$model = $this->_paymentRecords->create()->getCollection()->addFieldToFilter("order_id", ['eq' => $order->getId()]);

			$this->_inlineTranslation->suspend();
			$fromEmail = $this->_scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
			$fromName = $this->_scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);

			$sender = [
				'name' => $fromName,
				'email' => $fromEmail,
			];

			$transport = $this->_transportBuilder
				->setTemplateIdentifier('offline_payment_update')
				->setTemplateOptions(
					[
						'area' => 'frontend',
						'store' => $order->getStoreId(),
					]
				)
				->setTemplateVars([
					"orderid" => $order->getIncrementId(),
					'name' => $order->getCustomerName(),
					'paymentarray' => $model->getData(),
				])
				->setFromByScope($sender)
				->addTo([$order->getCustomerEmail()])
				->getTransport();
			$transport->sendMessage();
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}