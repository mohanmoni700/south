<?php
namespace Alfakher\MyDocument\Block\Index;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Filesystem\Io\File;

class Data extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \HookahShisha\ChangePassword\Plugin\CustomerSessionContext
     */
    protected $CustomerSessionContext;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \HookahShisha\ChangePassword\Plugin\CustomerSessionContext $CustomerSessionContext
     * @param CollectionFactory $collection
     * @param CustomerFactory $customer
     * @param AddressFactory $address
     * @param File $file
     * @param array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \HookahShisha\ChangePassword\Plugin\CustomerSessionContext $CustomerSessionContext,
        CollectionFactory $collection,
        CustomerFactory $customer,
        AddressFactory $address,
        File $file,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->customer = $customer;
        $this->CustomerSessionContext = $CustomerSessionContext;
        $this->address = $address;
        $this->collection = $collection;
        $this->file = $file;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->CustomerSessionContext->getCustomerId();
    }

    /**
     * @inheritDoc
     */
    public function getCustomercollection($customerid)
    {
        $customer = $this->customer->create()->load($customerid);
        return $customer;
    }

    /**
     * @inheritDoc
     */
    public function getCustomeraddress($customerid)
    {
        $customer = $this->customer->create()->load($customerid);
        $billingAddressId = $customer->getDefaultBilling();
        $billingAddress = $this->address->create()->load($billingAddressId);
        return $billingAddress;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentCollection($customerid)
    {
        $collection = $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $customerid]);
        return $collection;
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
        $pathInfo = $this->file->getPathInfo($file, PATHINFO_EXTENSION);
        return $pathInfo;
    }
}
