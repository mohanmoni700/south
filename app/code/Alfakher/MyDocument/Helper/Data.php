<?php

declare(strict_types=1);

namespace Alfakher\MyDocument\Helper;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
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

    const HOOKAH_WHOLESALERS_EXPIRY_DOC_EMAIL = 'hookah_wholesalers_expiry_doc_email';

    const CUSTOM_EXPIRY_DOC_EMAIL = 'custom_expiry_doc_email';

    const HW_WEB_CODE = 'hookah_wholesalers';

    /**
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customer
     * @param CollectionFactory $collection
     * @param AddressRepositoryInterface $addressRepositoryInterface
     * @param File $filesystem
     */
    public function __construct(
        Context                    $context,
        StateInterface             $inlineTranslation,
        TransportBuilder           $transportBuilder,
        StoreManagerInterface      $storeManager,
        CustomerFactory            $customer,
        CollectionFactory          $collection,
        AddressRepositoryInterface $addressRepositoryInterface,
        File                       $filesystem
    )
    {
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->customer = $customer;
        $this->collection = $collection;
        $this->storeManager = $storeManager;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function getCustomercollection($customerid)
    {
        return $this->customer->create()->load($customerid);
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

        $customer = $this->getCustomercollection($customerid);
        $customerEmail = $customer->getEmail();
        $customerName = $customer->getFirstname();
        $rejectedDoc = [];

        foreach ($post as $val) {
            $docname = $val['document_name'];
            $docmsg = $val['message'];
            $rejectedDoc[] = ['docmsg' => $docmsg, 'docname' => $docname];
        }

        $storeId = $customer->getData('store_id');

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
                    'store' => $storeId,/** Passed storeId here [BS]*/
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
            $customer = $this->getCustomercollection($customerid);
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

            $storeId = $customer->getData('store_id');

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
                ->setTemplateIdentifier(($this->getWebsiteCode($storeId) == self::HW_WEB_CODE) ?
                    self::HOOKAH_WHOLESALERS_EXPIRY_DOC_EMAIL : self::CUSTOM_EXPIRY_DOC_EMAIL)
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
        return $this->_scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
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
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @inheritDoc
     */
    public function checkExtension($file)
    {
        return $this->filesystem->getPathInfo($file, PATHINFO_EXTENSION);
    }

    /**
     * @param $storeId
     * @return string|null
     */
    public function getWebsiteCode($storeId)
    {
        $websiteCode = null;
        try {
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
        } catch (\Exception $exception) {
            $this->_logger->error('Expiry document email error :' . $exception->getMessage());
        }

        return $websiteCode;
    }
}
