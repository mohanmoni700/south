<?php

namespace Alfakher\OfflinePaymentRecords\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecordFactory $paymentRecords
     * @param \Alfakher\OfflinePaymentRecords\ViewModel\Helper $viewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecordFactory $paymentRecords,
        \Alfakher\OfflinePaymentRecords\ViewModel\Helper $viewHelper,
        array $data = []
    ) {
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->storeManager = $storeManager;
        $this->_paymentRecords = $paymentRecords;
        $this->_viewHelper = $viewHelper;
    }

    /**
     * Send mail
     *
     * @param mixed $order
     */
    public function sendMail($order)
    {
        try {

            $model = $this->_paymentRecords->create()->getCollection()->addFieldToFilter("order_id", ['eq' => $order->getId()]);

            $this->_inlineTranslation->suspend();
            $fromEmail = $this->_scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE, $order->getStoreId());
            $fromName = $this->_scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE, $order->getStoreId());

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
                    'grandtotal' => $order->getGrandtotal(),
                    'totalpaid' => $this->_viewHelper->getTotalPaidAmount($order->getId()),
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
