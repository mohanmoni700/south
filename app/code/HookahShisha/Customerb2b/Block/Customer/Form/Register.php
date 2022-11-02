<?php
namespace HookahShisha\Customerb2b\Block\Customer\Form;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\ObjectManager;
use Magento\Newsletter\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Helper\Data;
use Magento\Framework\Json\EncoderInterface;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as Countrycollection;
use Magento\Framework\Module\Manager;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use HookahShisha\Customerb2b\Model\Company\Source\Businesstype;
use HookahShisha\Customerb2b\Model\Company\Source\AnnualTurnOver;
use HookahShisha\Customerb2b\Model\Company\Source\HearAboutUs;
use HookahShisha\Customerb2b\Model\Company\Source\NumberOfEmp;
use Magento\Customer\Model\Metadata\Form;

/**
 * Customer register form block
 */
class Register extends \Magento\Directory\Block\Data
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var NewsletterConfig
     */
    private $newsLetterConfig;

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
     * @param CollectionFactory $countryCollectionFactory
     * @param Manager $moduleManager
     * @param Session $customerSession
     * @param Url $customerUrl
     * @param Businesstype $businesstype
     * @param AnnualTurnOver $annualTurnOver
     * @param HearAboutUs $hearAboutUs
     * @param NumberOfEmp $numberOfEmp
     * @param NewsletterConfig $newsLetterConfig
     * @param Address|null $addressHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        CollectionFactory $regionCollectionFactory,
        Countrycollection $countryCollectionFactory,
        Manager $moduleManager,
        Session $customerSession,
        Url $customerUrl,
        Businesstype $businesstype,
        AnnualTurnOver $annualTurnOver,
        HearAboutUs $hearAboutUs,
        NumberOfEmp $numberOfEmp,
        Config $newsLetterConfig = null,
        Address $addressHelper = null,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $data['addressHelper'] = $addressHelper ?: ObjectManager::getInstance()->get(Address::class);
        $data['directoryHelper'] = $directoryHelper;
        $this->_customerUrl = $customerUrl;
        $this->_moduleManager = $moduleManager;
        $this->_customerSession = $customerSession;
        $this->newsLetterConfig = $newsLetterConfig ?: ObjectManager::getInstance()->get(Config::class);
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
        $this->_isScopePrivate = false;
        $this->businesstype = $businesstype;
        $this->annualTurnOver = $annualTurnOver;
        $this->hearAboutUs = $hearAboutUs;
        $this->numberOfEmp = $numberOfEmp;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve form business type
     *
     * @return mixed
     */
    public function getBusinessType()
    {
        return $this->businesstype->toOptionArray();
    }

    /**
     * Retrieve form AnnualTurnOver
     *
     * @return mixed
     */
    public function getAnnualTurnOver()
    {
        return $this->annualTurnOver->toOptionArray();
    }

    /**
     * Retrieve form HearAboutUs
     *
     * @return mixed
     */
    public function getHearAboutUs()
    {
        return $this->hearAboutUs->toOptionArray();
    }

    /**
     * Retrieve form NumberOfEmp
     *
     * @return mixed
     */
    public function getNumberOfEmp()
    {
        return $this->numberOfEmp->toOptionArray();
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->_customerUrl->getRegisterPostUrl();
    }

    /**
     * Retrieve back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        $url = $this->getData('back_url');
        if ($url === null) {
            $url = $this->_customerUrl->getLoginUrl();
        }
        return $url;
    }

    /**
     * Retrieve form data
     *
     * @return mixed
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->_customerSession->getCustomerFormData(true);
            $data = new \Magento\Framework\DataObject();
            if ($formData) {
                $data->addData($formData);
                $data->setCustomerData(1);
            }
            if (isset($data['region_id'])) {
                $data['region_id'] = (int) $data['region_id'];
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Retrieve customer country identifier
     *
     * @return int
     */
    public function getCountryId()
    {
        $countryId = $this->getFormData()->getCountryId();
        if ($countryId) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Retrieve customer region identifier
     *
     * @return mixed
     */
    public function getRegion()
    {
        $formData = $this->getFormData();
        if (null !== ($region = $formData->getRegion())) {
            return $region;
        } elseif (null !== ($region = $formData->getRegionId())) {
            return $region;
        }
        return null;
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
     * Restore entity data from session
     *
     * Entity and form code must be defined for the form
     *
     * @param \Magento\Customer\Model\Metadata\Form $form
     * @param string|null $scope
     * @return $this
     */
    public function restoreSessionData(Form $form, $scope = null)
    {
        if ($this->getFormData()->getCustomerData()) {
            $request = $form->prepareRequest($this->getFormData()->getData());
            $data = $form->extractData($request, $scope, false);
            $form->restoreData($data);
        }

        return $this;
    }

    /**
     * Get minimum password length
     *
     * @return string
     * @since 100.1.0
     */
    public function getMinimumPasswordLength()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * Get number of password required character classes
     *
     * @return string
     * @since 100.1.0
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
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
