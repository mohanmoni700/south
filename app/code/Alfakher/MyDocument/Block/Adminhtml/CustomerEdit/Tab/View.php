<?php
namespace Alfakher\MyDocument\Block\Adminhtml\CustomerEdit\Tab;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\FormKey;

class View extends \Magento\Backend\Block\Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    protected $fileFactory;

    protected $_template = 'tab/customer_view.phtml';

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

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    public function getCustomer()
    {

        return $this->_coreRegistry->registry('current_customer_id');
    }

    public function getTabLabel()
    {
        return __('Documents');
    }

    public function getTabTitle()
    {
        return __('Documents');
    }

    public function getCustomercollection($customerid)
    {
        $customer = $this->customer->create()->load($customerid);
        return $customer;
    }

    public function myfunction($id)
    {
        $customerdata = $this->customer->create()->load($id);
        return $customerdata;

    }

    public function getDocumentCollection()
    {
        $collection = $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $this->_coreRegistry->registry('current_customer_id')]);
        return $collection;
    }

    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    public function getTabClass()
    {
        return '';
    }

    public function getTabUrl()
    {
        return '';
    }

    public function isAjaxLoaded()
    {
        return false;
    }
}
