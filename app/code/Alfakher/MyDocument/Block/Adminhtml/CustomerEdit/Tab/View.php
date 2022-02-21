<?php
namespace Alfakher\MyDocument\Block\Adminhtml\CustomerEdit\Tab;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\FormKey;

class View extends \Magento\Backend\Block\Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerFactory
     */
    protected $customer;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var _template
     */
    protected $_template = 'tab/customer_view.phtml';

    /**
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param DirectoryList $directory
     * @param CustomerFactory $customer
     * @param CollectionFactory $collection
     * @param FormKey $formKey
     * @param array $data = []
     */
    public function __construct(
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        DirectoryList $directory,
        CustomerFactory $customer,
        CollectionFactory $collection,
        FormKey $formKey,
        array $data = []
    ) {
        $this->fileFactory = $fileFactory;
        $this->_directory = $directory;
        $this->_coreRegistry = $registry;
        $this->customer = $customer;
        $this->collection = $collection;
        $this->formKey = $formKey;
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
        return $this->_coreRegistry->registry('current_customer_id');
    }

    /**
     * @inheritDoc
     */
    public function getTabLabel()
    {
        return __('Documents');
    }

    /**
     * @inheritDoc
     */
    public function getTabTitle()
    {
        return __('Documents');
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
    public function myfunction($id)
    {
        $customerdata = $this->customer->create()->load($id);
        return $customerdata;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentCollection()
    {
        $collection = $this->collection->create()
            ->addFieldToFilter('customer_id', ['eq' => $this->_coreRegistry->registry('current_customer_id')]);
        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }
}
