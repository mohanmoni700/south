<?php

namespace HookahShisha\Customerb2b\Block\Account;

use \Magento\Catalog\Model\Product\Attribute\Source\Status;

class MyProducts extends \Magento\Catalog\Block\Product\AbstractProduct
{

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface
     */
    protected $productItemRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $iterator;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem\CollectionFactory
     */
    protected $sharedCatalogCollection;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $productItemRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Framework\Model\ResourceModel\Iterator $iterator
     * @param \Magento\SharedCatalog\Model\ResourceModel\ProductItem\CollectionFactory $sharedCatalogCollection
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data = []
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $productItemRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Model\ResourceModel\Iterator $iterator,
        \Magento\SharedCatalog\Model\ResourceModel\ProductItem\CollectionFactory $sharedCatalogCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productItemRepository = $productItemRepository;
        $this->productRepository = $productRepository;
        $this->urlHelper = $urlHelper;
        $this->iterator = $iterator;
        $this->sharedCatalogCollection = $sharedCatalogCollection;
        $this->productFactory = $productFactory;
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
            $collection = $this->productFactory->create()->getCollection();

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
