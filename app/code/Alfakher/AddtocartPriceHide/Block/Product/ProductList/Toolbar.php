<?php
namespace Alfakher\AddtocartPriceHide\Block\Product\ProductList;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory as DocumentCollectionFactory;
use Alfakher\Productpageb2b\Helper\Data;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;

/**
 * Product list toolbar
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /**
     * Products collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_collection = null;

    /**
     * List of available order fields
     *
     * @var array
     */
    protected $_availableOrder = null;

    /**
     * List of available view types
     *
     * @var array
     */
    protected $_availableMode = [];

    /**
     * Is enable View switcher
     *
     * @var bool
     */
    protected $_enableViewSwitcher = true;

    /**
     *
     * @var bool
     */
    protected $_isExpanded = true;

    /**
     * Default Order field
     *
     * @var string
     */
    protected $_orderField = null;

    /**
     * Default direction
     *
     * @var string
     */
    protected $_direction = ProductList::DEFAULT_SORT_DIRECTION;

    /**
     * Default View mode
     *
     * @var string
     */
    protected $_viewMode = null;

    /**
     * @var bool $_paramsMemorizeAllowed
     * @deprecated 103.0.1
     */
    protected $_paramsMemorizeAllowed = true;

    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::product/list/toolbar.phtml';

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     *
     * @var \Magento\Catalog\Model\Session
     * @deprecated 103.0.1
     */
    protected $_catalogSession;

    /**
     * @var ToolbarModel
     */
    protected $_toolbarModel;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * @var ProductList
     */
    protected $_productListHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var DocumentCollectionFactory
     */
    protected $documentcollection;
    /**
     * @var helperData
     */
    protected $helperData;

    /**
     * Extend toolbar file
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param ToolbarModel $toolbarModel
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param ProductList $productListHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory $documentcollection
     * @param Data $helperData
     * @param ToolbarMemorizer|null $toolbarMemorizer
     * @param \Magento\Framework\App\Http\Context|null $httpContext
     * @param \Magento\Framework\Data\Form\FormKey|null $formKey
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Customer\Model\Session $customerSession,
        DocumentCollectionFactory $documentcollection,
        Data $helperData,
        ToolbarMemorizer $toolbarMemorizer,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {

        $this->toolbarMemorizer = $toolbarMemorizer;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->documentcollection = $documentcollection;
        $this->helperData = $helperData;
        return parent::__construct($context, $catalogSession, $catalogConfig, $toolbarModel, $urlEncoder, $productListHelper, $postDataHelper, $data, $toolbarMemorizer, $httpContext, $formKey); //phpcs:ignore
    }

    /**
     * Override load Available orders
     *
     * @return $this
     * @deprecated 103.0.1
     */
    private function loadAvailableOrders()
    {
        if ($this->_availableOrder === null) {
            $sortOrderArray = $this->_catalogConfig->getAttributeUsedForSortByArray();

            if (!$this->getCustomerBasedPriceSort()) {
                unset($sortOrderArray['price']);
            }

            $this->_availableOrder = $sortOrderArray;

        }
        return $this;
    }

    /**
     * Override getCurrentOrder
     *
     * @return $this
     * @deprecated 103.0.1
     */
    public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrders();
        $defaultOrder = $this->getOrderField();
        if (!$this->getCustomerBasedPriceSort()) {
            unset($orders['price']);
        }

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->toolbarMemorizer->getOrder();
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }

        if ($this->toolbarMemorizer->isMemorizingAllowed()) {
            $this->httpContext->setValue(ToolbarModel::ORDER_PARAM_NAME, $order, $defaultOrder);
        }

        $this->setData('_current_grid_order', $order);
        return $order;
    }

    /**
     * Override getAvailableOrders
     *
     * @return $this
     * @deprecated 103.0.1
     */
    public function getAvailableOrders()
    {
        $this->loadAvailableOrders();
        $orders = $this->_availableOrder;
        if (!$this->getCustomerBasedPriceSort()) {
            unset($orders['price']);
            $this->_availableOrder = $orders;
        }
        return $this->_availableOrder;
    }

    /**
     * Override getAvailableOrders
     *
     * @return bool
     * @deprecated 103.0.1
     */
    public function getCustomerBasedPriceSort()
    {
        if ($this->getCustomerIsLoggedIn()) {
            $customerid = $this->getCustomerId();
            $isFinanceVerified = $this->helperData->getIsFinanceVerified();
            /*
            Here we removed the condition of the Document verified
            ($is_document_upload $doc_expired) and uploaded($isFinanceVerified)
             */
            if ($isFinanceVerified == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Override getDocumentCollection
     *
     * @param int $customerid
     * @return bool
     * @deprecated 103.0.1
     */
    public function getDocumentCollection($customerid)
    {
        $documentcollection = $this->documentcollection->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerid]);
        if ($documentcollection->getSize()) {
            $notapprovetcollection = $this->documentcollection->create()
                ->addFieldToFilter('customer_id', ['eq' => $customerid])
                ->addFieldToFilter('status', ['eq' => 0]);
            if ($notapprovetcollection->getSize()) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
        return $documentcollection;
    }

    /**
     * Override getCustomerIsLoggedIn
     *
     * @return bool
     * @deprecated 103.0.1
     */
    public function getCustomerIsLoggedIn()
    {
        return (bool) $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Override getCustomerIsLoggedIn
     *
     * @return string
     * @deprecated 103.0.1
     */
    public function getCustomerId()
    {
        return $this->httpContext->getValue('customer_id');
    }
}
