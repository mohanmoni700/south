<?php
namespace Alfakher\MyDocument\Block\Adminhtml\CustomerEdit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;

class AddDocuments extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var \Alfakher\MyDocument\Helper\Data
     */
    protected $documentHelperData;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory
     */
    protected $documentCollection;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param FormKey $formKey
     * @param \Alfakher\MyDocument\Helper\Data $documentHelperData
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory $documentCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        FormKey $formKey,
        \Alfakher\MyDocument\Helper\Data $documentHelperData,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory $documentCollection,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->formKey = $formKey;
        $this->documentHelperData = $documentHelperData;
        $this->customerFactory = $customerFactory;
        $this->documentCollection = $documentCollection;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function getCustomer()
    {
        return $this->customerFactory->create()->load($this->getCustomerId());
    }

    /**
     * @inheritDoc
     */
    public function isCustomerFromUsa()
    {
        return $this->documentHelperData->isCustomerFromUsa($this->getCustomer());
    }

    /**
     * @inheritDoc
     */
    public function getDocumentCollection()
    {
        return $this->documentCollection->create()
            ->addFieldToFilter('customer_id', ['eq' => $this->getCustomerId()]);
    }

    /**
     * @inheritDoc
     */
    public function getMediaUrl()
    {
        return $this->documentHelperData->getMediaUrl();
    }

    /**
     * @inheritDoc
     */
    public function checkExtension($file)
    {
        return $this->documentHelperData->checkExtension($file);
    }
}
