<?php

declare (strict_types=1);

namespace Ooka\OokaSerialNumber\ViewModel;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Alfakher\Productpageb2b\Helper\Data;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Header;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ooka\OokaSerialNumber\Api\SerialNumberRepositoryInterface;

class SerialCode extends Data implements ArgumentInterface
{
    private const PRODUCT_OOKA_REQUIRE_SERIAL_NUMBER = 'ooka_require_serial_number';
    /**
     * @var SerialNumberRepositoryInterface
     */
    protected $serialNumberRepository;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Session $session
     * @param CollectionFactory $collection
     * @param Context $httpContext
     * @param CompanyManagementInterface $companyRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Header $httpHeader
     * @param CustomerFactory $customerFactory
     * @param StockStateInterface $stockInterface
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $proRepo
     * @param StockRegistryInterface $stockRegistry
     * @param SerialNumberRepositoryInterface $serialNumberRepository
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Session $session,
        CollectionFactory $collection,
        Context $httpContext,
        CompanyManagementInterface $companyRepository,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        Header $httpHeader,
        CustomerFactory $customerFactory,
        StockStateInterface $stockInterface,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $proRepo,
        StockRegistryInterface $stockRegistry,
        SerialNumberRepositoryInterface $serialNumberRepository,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaInterface $searchCriteria,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct(
            $session,
            $collection,
            $httpContext,
            $companyRepository,
            $customerRepository,
            $scopeConfig,
            $httpHeader,
            $customerFactory,
            $stockInterface,
            $storeManager,
            $proRepo,
            $stockRegistry
        );
        $this->serialNumberRepository = $serialNumberRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get all serial numbers
     *
     * @param int $sku
     * @param int $orderId
     * @param int $itemId
     * @return array
     */
    public function getSerialNumbers($sku, $orderId, $itemId)
    {
        $items = $this->serialNumberRepository->getCollection()->addFieldToFilter('main_table.sku', [
                'eq' => $sku
        ])->addFieldToFilter('main_table.order_id', [
            'eq' => $orderId
        ])->getItems();
        $itemsSerialCodes = [];
        foreach ($items as $item) {
            $itemsSerialCodes[] = $item->getData('serial_code');
        }
        return $itemsSerialCodes;
    }

    /**
     * Is Attribute is Enabled
     *
     * @param string $sku
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isAttributeEnabled($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (Exception $exception) {
            return 0;
        }
        return $product->getData(self::PRODUCT_OOKA_REQUIRE_SERIAL_NUMBER);
    }
}
