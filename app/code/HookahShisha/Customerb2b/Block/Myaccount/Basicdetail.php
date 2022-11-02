<?php
namespace HookahShisha\Customerb2b\Block\Myaccount;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\Config as NewsletterConfig;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Helper\Data;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as Countrycollection;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Module\Manager;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Block\Widget\Name;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Basic detail edit block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Basicdetail extends \Magento\Directory\Block\Data
{
    /**
     * @var AddressInterface|null
     */
    protected $_address = null;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @var NewsletterConfig
     */
    private $newsLetterConfig;

    /**
     * @var Subscriber
     */
    protected $subscription;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param CollectionFactory $regionCollectionFactory
     * @param Countrycollection $countryCollectionFactory
     * @param Session $customerSession
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CurrentCustomer $currentCustomer
     * @param DataObjectHelper $dataObjectHelper
     * @param SubscriberFactory $subscriberFactory
     * @param Manager $moduleManager
     * @param AddressMetadataInterface $addressMetadata = null
     * @param Address $addressHelper = null
     * @param NewsletterConfig $newsLetterConfig = null
     * @param StoreManagerInterface $storeManager
     * @param array $data = []
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        CollectionFactory $regionCollectionFactory,
        Countrycollection $countryCollectionFactory,
        Session $customerSession,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        CurrentCustomer $currentCustomer,
        DataObjectHelper $dataObjectHelper,
        SubscriberFactory $subscriberFactory,
        Manager $moduleManager,
        AddressMetadataInterface $addressMetadata = null,
        Address $addressHelper = null,
        NewsletterConfig $newsLetterConfig = null,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->currentCustomer = $currentCustomer;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressMetadata = $addressMetadata ?: ObjectManager::getInstance()->get(AddressMetadataInterface::class);
        $data['addressHelper'] = $addressHelper ?: ObjectManager::getInstance()->get(Address::class);
        $data['directoryHelper'] = $directoryHelper;
        $this->_moduleManager = $moduleManager;
        $this->newsLetterConfig = $newsLetterConfig ?: ObjectManager::getInstance()->get(Config::class);
        $this->subscriberFactory = $subscriberFactory;
        $this->storeManager = $storeManager;

        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->initAddressObject();

        $this->pageConfig->getTitle()->set($this->getTitle());

        if ($postedData = $this->_customerSession->getAddressFormData(true)) {
            $postedData['region'] = [
                'region_id' => isset($postedData['region_id']) ? $postedData['region_id'] : null,
                'region' => $postedData['region'],
            ];
            $this->dataObjectHelper->populateWithArray(
                $this->_address,
                $postedData,
                AddressInterface::class
            );
        }
        $this->precheckRequiredAttributes();
        return $this;
    }

    /**
     * Initialize address object.
     *
     * @return void
     */
    private function initAddressObject()
    {
        // Init address object
        $addressId = $this->getCustomer()->getDefaultBilling();
        if ($addressId) {
            try {
                $this->_address = $this->_addressRepository->getById($addressId);
                if ($this->_address->getCustomerId() != $this->_customerSession->getCustomerId()) {
                    $this->_address = null;
                }
            } catch (NoSuchEntityException $e) {
                $this->_address = null;
            }
        }

        if ($this->_address === null || !$this->_address->getId()) {
            $this->_address = $this->addressDataFactory->create();
            $customer = $this->getCustomer();
            $this->_address->setPrefix($customer->getPrefix());
            $this->_address->setFirstname($customer->getFirstname());
            $this->_address->setMiddlename($customer->getMiddlename());
            $this->_address->setLastname($customer->getLastname());
            $this->_address->setSuffix($customer->getSuffix());
        }
    }

    /**
     * Precheck attributes that may be required in attribute configuration.
     *
     * @return void
     */
    private function precheckRequiredAttributes()
    {
        $precheckAttributes = $this->getData('check_attributes_on_render');
        $requiredAttributesPrechecked = [];
        if (!empty($precheckAttributes) && is_array($precheckAttributes)) {
            foreach ($precheckAttributes as $attributeCode) {
                $attributeMetadata = $this->addressMetadata->getAttributeMetadata($attributeCode);
                if ($attributeMetadata && $attributeMetadata->isRequired()) {
                    $requiredAttributesPrechecked[$attributeCode] = $attributeCode;
                }
            }
        }
        $this->setData('required_attributes_prechecked', $requiredAttributesPrechecked);
    }

    /**
     * Generate name block html.
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock(Name::class)
            ->setForceUseCustomerAttributes(true)
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * Return the title, either editing an existing address, or adding a new one.
     *
     * @return string
     */
    public function getTitle()
    {
        return __('My Account');
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'customer/address/formPost',
            ['_secure' => true, 'id' => $this->getAddress()->getId()]
        );
    }

    /**
     * Return the associated address.
     *
     * @return AddressInterface
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Return the country Id.
     *
     * @return int|null|string
     */
    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? '' : $region->getRegion();
    }

    /**
     * Return the id of the region being edited.
     *
     * @return int region id
     */
    public function getRegionId()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? 0 : $region->getRegionId();
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->currentCustomer->getCustomer();
    }

    /**
     * Get config value.
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     */
    public function isNewsletterEnabled()
    {
        return $this->_moduleManager->isOutputEnabled('Magento_Newsletter')
        && $this->newsLetterConfig->isActive(ScopeInterface::SCOPE_STORE);
    }

    /**
     * Create an instance of a subscriber.
     *
     * @return Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->subscriberFactory->create();
    }

    /**
     * Retrieve the subscription object (i.e. the subscriber).
     *
     * @return Subscriber
     */
    public function getSubscriptionObject()
    {
        if ($this->subscription === null) {
            $websiteId = (int) $this->_storeManager->getWebsite()->getId();
            $this->subscription = $this->_createSubscriber();
            $this->subscription->loadByCustomer((int) $this->getCustomer()->getId(), $websiteId);
        }

        return $this->subscription;
    }

    /**
     * Get the customer is subscribed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }

    /**
     * Get contact_name value.
     *
     * @param string $contactname
     * @return array
     */
    public function getContactDetail()
    {
        $contact = [];
        $customerData = $this->getCustomer();
        $contactname = $customerData->getCustomAttribute('contact_name');
        $contactphone = $customerData->getCustomAttribute('contact_phone');
        $contactemail = $customerData->getCustomAttribute('contact_email');
        $contact['contact_name'] = $contactname ? $contactname->getValue() : "";
        $contact['contact_phone'] = $contactphone ? $contactphone->getValue() : "";
        $contact['contact_email'] = $contactemail ? $contactemail->getValue() : "";

        return $contact;
    }
    /**
     * Get website code
     *
     * @return string|null
     */
    public function getWebsiteCode():  ? string
    {
        return $this->storeManager->getWebsite()->getCode();
    }
}
