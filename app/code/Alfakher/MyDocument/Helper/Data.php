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
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepositoryInterface;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customer
     * @param CollectionFactory $collection
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface
     * @param \Magento\Framework\Filesystem\Io\File $filesystem
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        StoreManagerInterface $storeManager,
        CustomerFactory $customer,
        CollectionFactory $collection,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
        \Magento\Framework\Filesystem\Io\File $filesystem,
        array $data = []
    ) {
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->customer = $customer;
        $this->collection = $collection;
        $this->storeManager = $storeManager;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->filesystem = $filesystem;
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
        $rejectedDoc = [];

        foreach ($post as $val) {
            $docname = $val['document_name'];
            $docmsg = $val['message'];
            $rejectedDoc[] = ['docmsg' => $docmsg, 'docname' => $docname];
        }

        // print_r($rejectedDoc);
        // die();

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

        $this->_inlineTranslation->suspend();
        $fromEmail = $this->_scopeConfig
            ->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE, $storeId);
        $fromName = $this->_scopeConfig
            ->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE, $storeId);

        $sender = [
            'name' => $fromName,
            'email' => $fromEmail,
        ];

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
                'rejected_doc' => $rejectedDoc,

            ])
            ->setFromByScope($sender)
            ->addTo([$customerEmail])
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch (\Exception $e) {
            return false;
        }
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

            $rejectedDoc = [];

            foreach ($post as $val) {
                $docname = $val;

                $rejectedDoc[] = ['docname' => $docname];
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
                    'documentarray' => $rejectedDoc,
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
    /**
     * @inheritDoc
     */
    public function getExpirymailEnable()
    {
        // Check expiry document mail enable from configuration
        $configPath = 'hookahshisha/productpage/productpageb2b_documents_expired_mail_enable';
        $enableMail = $this->_scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
        return $enableMail;
    }

    /**
     * @inheritDoc
     */
    public function isCustomerFromUsa($customer)
    {
        if ($customer) {
            $addressId = $customer->getDefaultBilling();
            if (!$addressId) {
                $addressId = $customer->getDefaultShipping();
            }
            if ($addressId) {
                try {
                    $address = $this->addressRepositoryInterface->getById($addressId);
                    if ($address && $address->getCountryId() == 'US') {
                        return true;
                    }
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    /**
     * @inheritDoc
     */
    public function checkExtension($file)
    {
        $pathInfo = $this->filesystem->getPathInfo($file, PATHINFO_EXTENSION);
        return $pathInfo;
    }
}
