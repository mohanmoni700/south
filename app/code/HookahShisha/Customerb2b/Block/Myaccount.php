<?php

namespace HookahShisha\Customerb2b\Block;

use Magento\Company\Api\CompanyManagementInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\Config;

// /extends \Magento\Framework\View\Element\Template
class Myaccount extends \Magento\Directory\Block\Data
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var CompanyManagementInterface
     */
    protected $companyRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param CompanyManagementInterface $companyRepository
     * @param array $data = []
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        CompanyManagementInterface $companyRepository,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->currentCustomer = $currentCustomer;
        $this->dataObjectHelper = $dataObjectHelper;
        $data['directoryHelper'] = $directoryHelper;
        $this->companyRepository = $companyRepository;
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
     * Returns the Magento Customer Model for this block
     *
     * @return CustomerInterface|null
     */
    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Customer Model
     *
     * @return CompanyManagementInterface|null
     */
    public function getCustomerCompany()
    {
        $companyId = $this->getCustomer()->getId();
        return $this->companyRepository->getByCustomerId($companyId);
    }

    /**
     * Messages Data
     *
     * @return array
     */
    public function getMessageData()
    {
        $companyData = $this->getCustomerCompany();
        $customerData = $this->getCustomer();
        $comAccountVerified = 1;
        $comDetailsChanged = 0;
        $comVerificationMessage = "";
        if ($companyData) {
            $comAccountVerified = $companyData->getComAccountVerified();
            $comDetailsChanged = $companyData->getComDetailsChanged();
            $comVerificationMessage = $companyData->getComVerificationMessage();
        }
        $cst_account_verified = $customerData->getCustomAttribute('cst_account_verified');
        $cstAccountVerified = $cst_account_verified ? $cst_account_verified->getValue() : 0;
        $cst_details_changed = $customerData->getCustomAttribute('cst_details_changed');
        $cstDetailsChanged = $cst_details_changed ? $cst_details_changed->getValue() : 1;

        $cst_verification_message = $customerData->getCustomAttribute('cst_verification_message');
        $cstVerificationMessage = $cst_verification_message ? $cst_verification_message->getValue() : "";

        $message = [];
        $isContactChanged = 1;
        if ($comAccountVerified == 0 && $cstAccountVerified == 0
            && ($cstDetailsChanged == 1 || $comDetailsChanged == 1)
        ) {
            $messageString = "Your details are under verification. you would receive an email once there is an update.";
            $message[] = ["pending", $messageString];
        }

        if ($comAccountVerified == 0 && $comDetailsChanged == 0) {
            if (empty($comVerificationMessage)) {
                $comVerificationMessage = "Some Of your details has been rejected. please update the same";
            }
            $message[] = ["reject", $comVerificationMessage];
        }
        if ($cstAccountVerified == 0 && $cstDetailsChanged == 0) {
            if (empty($cstVerificationMessage)) {
                $cstVerificationMessage = "Some Of your details has been rejected. please update the same";
            }
            $message[] = ["reject", $cstVerificationMessage];
        }

        if ($cstAccountVerified == 0 && $comAccountVerified == 0) {
            $isContactChanged = 0;
        }

        return [
            'message' => $message,
            'comAccountVerified' => $comAccountVerified,
            'comDetailsChanged' => $comDetailsChanged,
            'cstAccountVerified' => $cstAccountVerified,
            'cstDetailsChanged' => $cstDetailsChanged,
            'isContactChanged' => $isContactChanged,
        ];
    }
}
