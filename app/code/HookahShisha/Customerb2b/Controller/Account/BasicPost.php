<?php

namespace HookahShisha\Customerb2b\Controller\Account;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\Result\PageFactory;

/**
 * Customer Address Form Post Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BasicPost extends \Magento\Customer\Controller\Address implements HttpPostActionInterface
{
    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Mapper
     */
    private $customerAddressMapper;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param FormFactory $formFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param DataObjectProcessor $dataProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param RegionFactory $regionFactory
     * @param HelperData $helperData
     * @param CustomerRepositoryInterface $customerRepository
     * @param Filesystem $filesystem = null
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        FormFactory $formFactory,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        DataObjectProcessor $dataProcessor,
        DataObjectHelper $dataObjectHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        RegionFactory $regionFactory,
        HelperData $helperData,
        CustomerRepositoryInterface $customerRepository,
        Filesystem $filesystem = null
    ) {
        $this->regionFactory = $regionFactory;
        $this->helperData = $helperData;
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
        $this->customerRepository = $customerRepository;
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory
        );
    }

    /**
     * Extract address from request
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    protected function _extractAddress()
    {
        $existingAddressData = $this->getExistingAddressData();

        /** @var \Magento\Customer\Model\Metadata\Form $addressForm */
        $addressForm = $this->_formFactory->create(
            'customer_address',
            'customer_address_edit',
            $existingAddressData
        );
        $addressData = $addressForm->extractData($this->getRequest());
        $attributeValues = $addressForm->compactData($addressData);

        $this->updateRegionData($attributeValues);

        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            array_merge($existingAddressData, $attributeValues),
            \Magento\Customer\Api\Data\AddressInterface::class
        );

        return $addressDataObject;
    }

    /**
     * Retrieve existing address data
     *
     * @return array
     * @throws \Exception
     */
    protected function getExistingAddressData()
    {
        $existingAddressData = [];
        if ($addressId = $this->getRequest()->getParam('id')) {
            $existingAddress = $this->_addressRepository->getById($addressId);
            if ($existingAddress->getCustomerId() !== $this->_getSession()->getCustomerId()) {
                throw new NotFoundException(__('Address not found.'));
            }
            $existingAddressData = $this->getCustomerAddressMapper()->toFlatArray($existingAddress);
        }
        return $existingAddressData;
    }

    /**
     * Update region data
     *
     * @param array $attributeValues
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateRegionData(&$attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }

        $regionData = [
            RegionInterface::REGION_ID => !empty($attributeValues['region_id']) ? $attributeValues['region_id'] : null,
            RegionInterface::REGION => !empty($attributeValues['region']) ? $attributeValues['region'] : null,
            RegionInterface::REGION_CODE => !empty($attributeValues['region_code'])
            ? $attributeValues['region_code']
            : null,
        ];

        $region = $this->regionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $region,
            $regionData,
            \Magento\Customer\Api\Data\RegionInterface::class
        );
        $attributeValues['region'] = $region;
    }

    /**
     * Process address form save
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirectUrl = 'customer/account/index';
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath($redirectUrl);
        }

        if (!$this->getRequest()->isPost()) {
            $this->_getSession()->setAddressFormData($this->getRequest()->getPostValue());
            return $this->resultRedirectFactory->create()->setPath($redirectUrl);
        }

        try {
            $address = $this->_extractAddress();
            if ($this->_request->getParam('delete_attribute_value')) {
                $address = $this->deleteAddressFileAttribute($address);
            }
            $this->_addressRepository->save($address);
            /* Start Change the Customer objct Name */
            $currentCustomerDataObject = $this->getCustomerDataObject($this->_customerSession->getCustomerId());
            $extensionAttributes = $currentCustomerDataObject->getExtensionAttributes();
            $extensionAttributes->setIsSubscribed($this->getRequest()->getParam('is_subscribed', false));
            $currentCustomerDataObject->setExtensionAttributes($extensionAttributes);
            $currentCustomerDataObject->setFirstname($this->getRequest()->getParam('firstname'));
            $currentCustomerDataObject->setLastname($this->getRequest()->getParam('lastname'));

            if ($this->getRequest()->getParam('hub_mobile_number')) {
                $hub_mobile_number = $this->getRequest()->getParam('hub_mobile_number');
                $currentCustomerDataObject->setCustomAttribute('hub_mobile_number', $hub_mobile_number);
            }
            $this->customerRepository->save($currentCustomerDataObject);
            /* ENd Change the Customer objct Name */

            $this->messageManager->addSuccessMessage(__('You saved the basic details.'));
            return $this->resultRedirectFactory->create()->setPath($redirectUrl);
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t save the basic details.'));
            return $this->resultRedirectFactory->create()->setPath($redirectUrl);
        }

        $this->_getSession()->setAddressFormData($this->getRequest()->getPostValue());

        return $this->resultRedirectFactory->create()->setPath($redirectUrl);
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return CustomerInterface
     */
    private function getCustomerDataObject($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Get Customer Address Mapper instance
     *
     * @return Mapper
     *
     * @deprecated 100.1.3
     */
    private function getCustomerAddressMapper()
    {
        if ($this->customerAddressMapper === null) {
            $this->customerAddressMapper = ObjectManager::getInstance()->get(
                \Magento\Customer\Model\Address\Mapper::class
            );
        }
        return $this->customerAddressMapper;
    }

    /**
     * Removes file attribute from customer address and file from filesystem
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return mixed
     */
    private function deleteAddressFileAttribute($address)
    {
        $attributeValue = $address->getCustomAttribute($this->_request->getParam('delete_attribute_value'));
        if ($attributeValue !== null) {
            if ($attributeValue->getValue() !== '') {
                $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                $fileName = $attributeValue->getValue();
                $path = $mediaDirectory->getAbsolutePath('customer_address' . $fileName);
                if ($fileName && $mediaDirectory->isFile($path)) {
                    $mediaDirectory->delete($path);
                }
                $address->setCustomAttribute(
                    $this->_request->getParam('delete_attribute_value'),
                    ''
                );
            }
        }

        return $address;
    }
}
