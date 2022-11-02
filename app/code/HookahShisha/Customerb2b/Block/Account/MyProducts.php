<?php

namespace HookahShisha\Customerb2b\Block\Account;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Url\Helper\Data;
use Magento\SharedCatalog\Api\ProductItemRepositoryInterface;
use Magento\SharedCatalog\Model\ResourceModel\ProductItem\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class MyProducts extends \Magento\Catalog\Block\Product\AbstractProduct
{

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var ProductItemRepositoryInterface
     */
    protected $productItemRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Data
     */
    protected $urlHelper;

    /**
     * @var Iterator
     */
    protected $iterator;

    /**
     * @var CollectionFactory
     */
    protected $sharedCatalogCollection;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ProductContext $context
     * @param ProductFactory $productFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param HttpContext $httpContext
     * @param Iterator $iterator
     * @param Data $urlHelper
     * @param ProductItemRepositoryInterface $productItemRepository
     * @param CollectionFactory $sharedCatalogCollection
     * @param StoreManagerInterface $storeManager
     * @param array $data = []
     */

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductContext $context,
        ProductFactory $productFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        HttpContext $httpContext,
        Iterator $iterator,
        Data $urlHelper,
        ProductItemRepositoryInterface $productItemRepository,
        CollectionFactory $sharedCatalogCollection,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->httpContext = $httpContext;
        $this->iterator = $iterator;
        $this->urlHelper = $urlHelper;
        $this->productItemRepository = $productItemRepository;
        $this->sharedCatalogCollection = $sharedCatalogCollection;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Pagination'));
        if ($this->getSharedCatalogCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'my.product.pricing.pager'
            )->setAvailableLimit([20 => 20, 40 => 40, 60 => 60, 80 => 80])
                ->setShowPerPage(true)
                ->setCollection($this->getSharedCatalogCollection());
            $this->setChild('pager', $pager);
            $this->getSharedCatalogCollection()->load();
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @inheritdoc
     */
    public function getSharedCatalogCollection()
    {
        $collection = [];
        if ($customer = $this->getCurrentCustomer()) {
            $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
            $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 20;
            $collection = $this->productFactory->create()->getCollection()
                ->addStoreFilter($this->storeManager->getStore());

            $joinAndConditions[] = "u.sku = e.sku";
            $joinAndConditions[] = "u.customer_group_id= " . $customer->getGroupId();

            $joinConditions = implode(' AND ', $joinAndConditions);
            $collection->getSelect()->join(
                ['u' => $collection->getTable('shared_catalog_product_item')],
                $joinConditions,
                []
            );

            $joinAndConditionsTier[] = "t.row_id = e.row_id";
            $joinAndConditionsTier[] = "t.customer_group_id= " . $customer->getGroupId();

            $joinConditionsTier = implode(' AND ', $joinAndConditionsTier);
            $collection->getSelect()->join(
                ['t' => $collection->getTable('catalog_product_entity_tier_price')],
                $joinConditionsTier,
                []
            );

            $collection->addAttributeToSelect(['price', 'regular_price', 'final_price', 'name', 'small_image']);
            $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
            $collection->addAttributeToFilter('type_id', 'simple');
            $collection->setPageSize($pageSize);
            $collection->setCurPage($page);
            $collection->getSelect()->group('entity_id');
            /*For out of stock product display in last.*/
            $collection->setOrder('name', 'ASC');
            $collection->getSelect()->joinLeft(
                ['_inventory_table' => 'cataloginventory_stock_item'],
                "_inventory_table.product_id = e.entity_id",
                ['is_in_stock']
            );
            $collection->getSelect()->order(['is_in_stock desc']);
        }
        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function isCustomerLoggedIn()
    {
        return (bool) $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @inheritdoc
     */
    public function getCurrentCustomer()
    {
        if ($this->isCustomerLoggedIn()) {
            $currentCustomerId = $this->httpContext->getValue('customer_id');
            return $this->_customerRepositoryInterface->getById($currentCustomerId);
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function loadMyProduct($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * @inheritdoc
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED =>
                $this->urlHelper->getEncodedUrl($url),
            ],
        ];
    }
}
