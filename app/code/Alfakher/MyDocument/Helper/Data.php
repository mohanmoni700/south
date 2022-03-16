<?php
namespace Alfakher\MyDocument\Helper;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logLoggerInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customer
     * @param CollectionFactory $collection
     * @param array $data = []
     */

    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        StoreManagerInterface $storeManager,
        CustomerFactory $customer,
        CollectionFactory $collection,
        array $data = []
    ) {
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->customer = $customer;
        $this->collection = $collection;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function sendMail($post, $customerid)
    {
        foreach ($post as $value) {
            $x[] = $value['status'];
        }
        if (in_array(0, $x)) {
            $msg = "rejected";
        } else {
            $msg = "accepted";
        }

        $customer = $this->customer->create()->load($customerid);
        $customerEmail = $customer->getEmail();
        $customerName = $customer->getFirstname();

        $collection = $this->collection->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerid]);
        $docdata = $collection->getData();

        $rejected_doc = [];

        foreach ($docdata as $val) {
            $docname = $val['document_name'];
            $docmsg = $val['message'];
            $rejected_doc[] = ['docmsg' => $docmsg, 'docname' => $docname];
        }

        $this->_inlineTranslation->suspend();
        $fromEmail = $this->_scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
        $fromName = $this->_scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);

        $sender = [
            'name' => $fromName,
            'email' => $fromEmail,
        ];

        /** Get current storeId start[BS]*/
        $storeManagerDataList = $this->storeManager->getStores();
        $options = [];

        foreach ($storeManagerDataList as $key => $value) {
            $options[] = ['label' => $value['code'], 'value' => $key];
            if ($value['code'] == "hookah_wholesalers_store_view") {
                $storeId = $key;
            }
        }
        /** Get current storeId end[BS]*/

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier('custom_email')
            ->setTemplateOptions(
                [
                    'area' => 'frontend',
                    'store' => $storeId, /** Passed storeId here [BS]*/
                ]
            )

            ->setTemplateVars([
                'msg' => $msg,
                'name' => $customerName,
                'rejected_doc' => $rejected_doc,

            ])
            ->setFromByScope($sender)
            ->addTo([$customerEmail])
            ->getTransport();

        $transport->sendMessage();
    }
    /**
     * @inheritDoc
     */
    public function sendExpiryMail($post, $customerid)
    {
        try {
            $customer = $this->customer->create()->load($customerid);
            $customerEmail = $customer->getEmail();
            $customerName = $customer->getFirstname();

            $collection = $this->collection->create()
                ->addFieldToFilter('customer_id', ['eq' => $customerid]);
            $docdata = $collection->getData();

            $rejected_doc = [];

            foreach ($post as $val) {
                $docname = $val;

                $rejected_doc[] = ['docname' => $docname];
            }

            $this->_inlineTranslation->suspend();
            $fromEmail = $this->_scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $fromName = $this->_scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);

            $sender = [
                'name' => $fromName,
                'email' => $fromEmail,
            ];

            /** Get current storeId start[BS]*/
            $storeManagerDataList = $this->storeManager->getStores();
            $options = [];

            foreach ($storeManagerDataList as $key => $value) {
                $options[] = ['label' => $value['code'], 'value' => $key];
                if ($value['code'] == "hookah_wholesalers_store_view") {
                    $storeId = $key;
                }
            }
            /** Get current storeId end[BS]*/

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('custom_expiry_doc_email')
                ->setTemplateOptions(
                    [
                        'area' => 'frontend',
                        /** passed storeId here [BS]*/
                        'store' => $storeId,
                    ]
                )

                ->setTemplateVars([
                    'name' => $customerName,
                    'documentarray' => $rejected_doc,
                ])
                ->setFromByScope($sender)
                ->addTo([$customerEmail])
                ->getTransport();

            $transport->sendMessage();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
