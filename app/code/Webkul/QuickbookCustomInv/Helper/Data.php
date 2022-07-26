<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_QuickbookCustomInv
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\QuickbookCustomInv\Helper;

use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use QuickBooksOnline\API\Data\IPPPhysicalAddress;

/**
 * MultiQuickbooksConnect data helper
 */
class Data extends \Webkul\MultiQuickbooksConnect\Helper\Data
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $cacheFrontendPool;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelperData;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Downloadable\Api\LinkRepositoryInterface
     */
    private $linkFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Tax\Item
     */
    private $itemTax;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Logger\Logger
     */
    private $logger;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Json\Helper\Data $jsonHelperData
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param LinkRepositoryInterface $linkFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Sales\Model\ResourceModel\Order\Tax\Item $itemTax
     * @param \Webkul\MultiQuickbooksConnect\Logger\Logger $logger
     * @param \Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface $accountRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Json\Helper\Data $jsonHelperData,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        LinkRepositoryInterface $linkFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Sales\Model\ResourceModel\Order\Tax\Item $itemTax,
        \Webkul\MultiQuickbooksConnect\Logger\Logger $logger,
        \Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface $accountRepository
    ) {
        parent::__construct(
            $context,
            $cacheTypeList,
            $cacheFrontendPool,
            $countryFactory,
            $curl,
            $configWriter,
            $dateTime,
            $encryptor,
            $jsonHelperData,
            $productRepository,
            $linkFactory,
            $filterManager,
            $itemTax,
            $logger,
            $accountRepository
        );
        /*$this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->countryFactory = $countryFactory;
        $this->curl = $curl;
        $this->configWriter = $configWriter;*/
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->accountRepository = $accountRepository;
        /*
        $this->jsonHelperData = $jsonHelperData;
        $this->productRepository = $productRepository;
        $this->linkFactory = $linkFactory;
        $this->filterManager = $filterManager;
        $this->itemTax = $itemTax;
        $this->logger = $logger;
        */
    }

    /**
     * getQuickbooksAccountConfig
     *
     * @param string $accountId
     * @return array
     */
    public function getQuickbooksAccountConfig($accountId)
    {
        $configData = $this->getQuickbooksConnectConfig();
        if ($configData) {
            $encryptor = $this->encryptor;
            $accountData = $this->accountRepository->getById($accountId)->getData();
            $accountConfig = [
                'enable' => $configData['enable'],
                'sales_receipt_create_on' => $accountData['sales_receipt_create_on'],
                'creditmemo_auto_sync' => $accountData['creditmemo_auto_sync'],
                'us_store' => $accountData['us_store'],
                'realm_id' => $accountData['realm_id'],
                'client_id' => $configData['client_id'],
                'client_secret' => $configData['client_secret'],
                'oauth2_access_token' => $encryptor->decrypt($accountData['oauth2_access_token']),
                'oauth2_access_token_expire_on' => $accountData['oauth2_access_token_expire_on'],
                'oauth2_refresh_token' => $encryptor->decrypt($accountData['oauth2_refresh_token']),
                'oauth2_refresh_token_expire_on' => $accountData['oauth2_refresh_token_expire_on'],
                'app_integrates_with' => $configData['app_integrates_with'],
                'account_type' => $configData['account_type'],
                'token_end_point_url' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
                'store_id' => $accountData['store_id'],
                'account_name' => $accountData['account_name'],
                'is_authenticated' => $accountData['is_authenticated'],
                'asset_account' => $accountData['asset_account'],
                'income_account' => $accountData['income_account'],
                'expense_account' => $accountData['expense_account'],
                'default_tax_class' => $accountData['default_tax_class']
            ];
            //$accountConfig = array_merge($accountConfig, $accountData);
            return $accountConfig;
        }
        return $configData;
    }

    /**
     * getArrangedItemDataForQuickbooks
     * @param Magento/Sales/Model/Order/Item $orderItem
     * @param Array $taxPercentDetail
     * @param int $qty
     * @return array
     */
    public function getArrangedItemDataForQuickbooks($orderItem, $taxPercentDetail, $qty = 0)
    {
        try {
            $product = $orderItem->getProduct();
            $quantityAndStockStatus = $product ? $product->getQuantityAndStockStatus() : ['qty' => 0];
            $productTypeArray = [
                'simple' => 'Inventory',
                'downloadable' => 'NonInventory',
                'virtual' => 'Service',
                'etickets' => 'Inventory'
            ];
            $typeId = $product ? $product->getTypeId() : 'simple';
            $ratePrice = 0;
            $bundleQty = [];
            if ($orderItem->getParentItemId() && $orderItem->getParentItem()->getProduct()) {
                $typeIdTemp = $orderItem->getParentItem()->getProduct()->getTypeId();
                $priceType = $orderItem->getParentItem()->getProduct()->getPriceType();
                if ($typeIdTemp != 'bundle') {
                    $orderItem = $orderItem->getParentItem();
                } elseif ($typeIdTemp == 'bundle' && $priceType) {
                    $options = $orderItem->getParentItem()->getProductOptions();
                    if (isset($options['bundle_options'])) {
                        $itemId = $orderItem->getParentItem()->getItemId();
                        foreach ($options['bundle_options'] as $optionsData) {
                            $bundleQty[$optionsData['value'][0]['title']] = $optionsData['value'][0]['qty'];
                        }
                        $ratePrice = $orderItem->getParentItem()->getBasePrice()/count($options['bundle_options']);
                    }
                }
            }
            $itemId = isset($itemId) ? $itemId : $orderItem->getItemId();
            $productData  = $this->getProductData($orderItem, $ratePrice, $bundleQty);
            $price = $productData['price'];
            $description = $productData['description'];
            $productName = $productData['productName'];
            $proUnitPrice = $productData['proUnitPrice'];
            $description = $description == "" ? $productData['productName'] : $description;
            $options = $orderItem->getProductOptions();
            $optionsLabel = '';
            if (isset($options['options']) || isset($options['attributes_info'])) {
                $options = isset($options['options']) ? $options['options'] : $options['attributes_info'];
                $optionsLabel = $this->getCustomOptionsWithValue($options);
                $optionsLabel = ' (Options Applied : '.$optionsLabel. ')';
            }
            $linksLabel = '';
            if (isset($options['links'])) {
                $linksLabel = $this->getDownloadableProLinkLables($orderItem->getProduct(), $options['links']);
                $linksLabel = ' (Downloadable Links : '.$linksLabel. ')';
            }

            $totalQty = isset($quantityAndStockStatus['qty']) ? $quantityAndStockStatus['qty'] : 0;
            $product = $orderItem->getProduct();
            $qty = $qty ? $qty : $orderItem->getQtyOrdered();
            $itemData = [
                'Name' => $this->validateStringLimit('Name', $orderItem->getName()),
                'UnitPrice' => $proUnitPrice,
                'MainPrice' => $price,
                'isTaxablePro' => $orderItem->getBaseTaxAmount() ? 1 : 0,
                'Taxable' => isset($taxPercentDetail[$itemId][0]) ? 1 : 0,
                'taxAmt' => $orderItem->getTaxAmount(),//$orderItem->getExciseTax() + $orderItem->getSalesTax(),
                'Sku' => $orderItem->getSku(),
                'Description' => $this->validateStringLimit('Description', $description),
                'OptionsDetail' => $optionsLabel.$linksLabel,
                'Qty' => $qty,
                'discountAmt' => $orderItem->getBaseDiscountAmount(),
                'AmountTotal' => $proUnitPrice * $qty,
                'Type' => $productTypeArray[$typeId],
                'TrackQtyOnHand' => in_array($typeId, ['simple','etickets']) ? 'true' : 'false',
                'QtyOnHand' => $quantityAndStockStatus['qty']+$orderItem->getQtyOrdered(),
                'InvStartDate' => $this->dateTime->date()->format('Y-m-d'),
                'ItemId' => $itemId
            ];
            return $itemData;
        } catch (\Exception $e) {
            $this->logger->addError('getArrangedItemDataForQuickbooks -'.$e->getMessage());
            throw new \Exception($e->getMessage(), 1);
        }
    }

    /**
     * getProductData
     * @param Magento/Sales/Model/Order/Item $orderItem
     * @param float $ratePrice
     * @param array $bundleQty
     */
    private function getProductData($orderItem, $ratePrice, $bundleQty)
    {
        $price =  $orderItem->getBasePrice();
        $product = $orderItem->getProduct();
        if ($product) {
            $productName = $product->getResource()->getAttributeRawValue($product->getEntityId(), 'name', 0);
            $description = $product->getDescription();
        } else {
            $productName = $orderItem->getName();
            $description = $orderItem->getDescription() ? $orderItem->getDescription() : $orderItem->getName();
        }
        $proUnitPrice = $orderItem->getBaseOriginalPrice();
        if ($ratePrice && !empty($bundleQty)) {
            foreach ($bundleQty as $key => $value) {
                if (strcasecmp($key, $productName) == 0) {
                    $bundleItemQty = $value;
                }
            }
            $price = $ratePrice/$bundleItemQty;
            $proUnitPrice = $this->productRepository->getById($orderItem->getProductId())->getPrice();
        }
        $productName = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $productName);
        $productData =  [
            'productName' => $productName,
            'proUnitPrice' => $price,
            'price' => $proUnitPrice,
            'description' => $description
        ];
        return $productData;
    }
}
