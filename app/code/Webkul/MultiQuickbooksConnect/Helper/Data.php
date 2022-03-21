<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Helper;

use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use QuickBooksOnline\API\Data\IPPPhysicalAddress;

/**
 * MultiQuickbooksConnect data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * HTTP Methods
     */
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_PATCH   = 'PATCH';

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
        parent::__construct($context);
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->countryFactory = $countryFactory;
        $this->curl = $curl;
        $this->configWriter = $configWriter;
        $this->dateTime = $dateTime;
        $this->encryptor = $encryptor;
        $this->jsonHelperData = $jsonHelperData;
        $this->productRepository = $productRepository;
        $this->linkFactory = $linkFactory;
        $this->filterManager = $filterManager;
        $this->itemTax = $itemTax;
        $this->logger = $logger;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelperData;
    }

    /**
     * getQuickbooksConnectConfig
     * @return array
     */
    public function getQuickbooksConnectConfig()
    {
        $path = 'wk_multi_quickbooks_connect/general_settings/';

        $encryptor = $this->encryptor;
        $config = [
            'enable' => $this->scopeConfig->getValue($path.'enable'),
            'client_id' => $this->scopeConfig->getValue($path.'client_id'),
            'client_secret' => $encryptor->decrypt($this->scopeConfig->getValue($path.'client_secret')),
            'app_integrates_with' => $this->scopeConfig->getValue($path.'app_integrates_with'),
            'account_type' => $this->scopeConfig->getValue($path.'account_type'),
            'token_end_point_url' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer'
        ];
        if ($config['app_integrates_with'] == 'oauth2') {
            $status = $config['client_id'] && $config['client_secret'];
        }
        return $status ? $config : false;
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
                'expense_account' => $accountData['expense_account']
            ];
            return $accountConfig;
        }
        return $configData;
    }

    /**
     * getScopeConfigValue
     * @return string
     */
    public function getScopeConfigValue($filed)
    {
        return $this->scopeConfig->getValue($filed);
    }

    /**
     * getEmailAddress
     * @param array $address
     * @return IPPPhysicalAddress
     */
    public function getPhysicalAddress($addressData)
    {
        try {
            $address = new IPPPhysicalAddress();
            $address->Line1 = $addressData['street'];
            $address->City = $addressData['city'];
            $address->Country = $this->countryFactory->create()->loadByCode($addressData['country_id'])->getName();
            $address->CountrySubDivisionCode = $addressData['region'];
            $address->PostalCode  = $addressData['postcode'];
            return $address;
        } catch (\Exception $e) {
            $this->logger->addError('getPhysicalAddress : '.$e->getMessage());
            return $address;
        }
    }

    /**
     * refreshAccessToken
     * @return string
     */
    public function refreshAccessToken($accountId)
    {
        try {
            $config = $this->getQuickbooksAccountConfig($accountId);
            if ($config) {
                $qbAccount = $this->accountRepository->getById($accountId);
                $oauth2LoginHelper = new OAuth2LoginHelper($config['client_id'], $config['client_secret']);
                $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken(
                    $config['oauth2_refresh_token']
                );
                $accessTokenValue = $accessTokenObj->getAccessToken();
                $refreshTokenValue = $accessTokenObj->getRefreshToken();
                if ($accessTokenObj && $refreshTokenValue && $accessTokenValue) {
                    $gmtCurrectTime = $this->dateTime->date();
                    $gmtCurrectTime = $this->dateTime->convertConfigTimeToUtc($gmtCurrectTime);
                    $accessTokenExpireOn = date(
                        'Y-m-d H:i:s',
                        strtotime('+'.(3570).' seconds', strtotime($gmtCurrectTime))
                    );
                    
                    $saveData = [
                        'oauth2_access_token' => $this->encryptor->encrypt($accessTokenValue),
                        'oauth2_refresh_token' => $this->encryptor->encrypt($refreshTokenValue),
                        'oauth2_access_token_expire_on' => $accessTokenExpireOn
                    ];
                    $qbAccount->addData($saveData)->setId($qbAccount->getId())->save();

                    $this->logger->addInfo('refreshAccessToken - access token updated for AccountId: '.$accountId);
                    return $accessTokenValue;
                } else {
                    $saveData = [
                        'oauth2_access_token' => $accessTokenValue,
                        'oauth2_refresh_token' => $refreshTokenValue,
                        'oauth2_access_token_expire_on' => $accessTokenExpireOn,
                        'oauth2_refresh_token_expire_on' => $refreshTokenExpireOn,
                        'is_authenticated' => 0,
                    ];
                    $qbAccount->addData($saveData)->setId($qbAccount->getId())->save();

                    $this->logger->addInfo('refreshAccessToken -expired for AccountId: '.$accountId);
                    return false;
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError('refreshAccessToken - '.$e->getMessage());
            return false;
        }
    }

    /**
     * cleanConfigCache
     */
    public function cleanConfigCache()
    {
        $this->cacheTypeList->cleanType('config');
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    /**
     * getAccessToken
     * @return string|false
     */
    public function getAccessToken($accountId)
    {
        try {
            $config = $this->getQuickbooksAccountConfig($accountId);
            if ($config) {
                $expireDate = strtotime($config['oauth2_access_token_expire_on']);
                $currentDate = $this->dateTime->date();
                $currentDate = strtotime($this->dateTime->convertConfigTimeToUtc($currentDate));
                if ($currentDate > $expireDate) {
                    return $this->refreshAccessToken($accountId);
                }
                return $config['oauth2_access_token'];
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->addError('getAccessToken - '.$e->getMessage());
            return false;
        }
    }

    /**
     * generateAuthorizationHeader
     * @param string $clientId
     * @param string $clientSecret
     * @return string
     */
    public function generateAuthorizationHeader()
    {
        try {
            $config = $this->getQuickbooksConnectConfig();
            if ($config && $config['client_id'] && $config['client_secret']) {
                $encodedClientIDClientSecrets = base64_encode($config['client_id'] . ':' . $config['client_secret']);
                $authorizationheader = 'Basic ' . $encodedClientIDClientSecrets;
                return $authorizationheader;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->addError('generateAuthorizationHeader - '.$e->getMessage());
            return false;
        }
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
        }
    }

    /**
     * getCustomOptionsWithValue
     * @param Magento/Catalog/Model/Product $product
     * @param int[] $linksArr
     * @return string
     */
    public function getDownloadableProLinkLables($product, $linksArr)
    {
        try {
            $links = $this->linkFactory->getLinksByProduct($product);
            $linkTitle = "";
            foreach ($links as $link) {
                if (in_array($link->getLinkId(), $linksArr)) {
                    $linkTitle .= $link->getTitle().', ';
                }
            }
            $linkTitle = rtrim($linkTitle, ", ");
            return $linkTitle;
        } catch (\Exception $e) {
            $this->logger->addError('getDownloadableProLinkLables :'.$e->getMessage());
            return "";
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

    /**
     * getCustomOptionsWithValue
     * @param array $options
     * @return string
     */
    public function getCustomOptionsWithValue($options)
    {
        $optionsLabel = '';
        foreach ($options as $option) {
            $optionsLabel = $optionsLabel.$option['label'].' => '.$option['value'].', ';
        }
        $optionsLabel = rtrim($optionsLabel, ", ");
        return $optionsLabel;
    }

    /**
     * validateStringLimit
     */
    public function validateStringLimit($type, $string)
    {
        switch ($type) {
            case 'Name':
                $string = substr($string, 0, 99);
                $string = trim($string);
                break;
            case 'Description':
                $string = $this->filterManager->stripTags($string);
                $string = substr($string, 0, 3999);
                break;
        }
        return $string;
    }

    /**
     * getOrderTaxPercent
     * @param int $orderId
     * @return array
     */
    public function getOrderTaxPercent($orderId)
    {
        $taxDetailList = $this->itemTax->getTaxItemsByOrderId($orderId);
        $taxDetail = [];
        foreach ($taxDetailList as $key => $item) {
            $taxDetail[$item['item_id']][] = $item;
        }
        ksort($taxDetail, SORT_NUMERIC);
        return $taxDetail;
    }

    /**
     * getCustomerDetailForQuickbooks
     * @param Magento/Sales/Model/Order $order
     * @return array $customerData
     */
    public function getCustomerDetailForQuickbooks($order)
    {
        $billingAddress = $order->getBillingAddress();
        $shipAddress = $order->getShippingAddress() ? $order->getShippingAddress() : $billingAddress;
        $isGuest = $order->getCustomerIsGuest();
        if ($isGuest) {
            $customerData = [
                'Title' => null,
                'Name' => $order->getCustomerName(),
                'CompanyName' => $billingAddress->getCompany(),
                'GivenName' => $billingAddress->getFirstname(),
                'MiddleName' => $billingAddress->getMiddlename(),
                'FamilyName' => $billingAddress->getLastname(),
                'DisplayName' => $order->getFirstname(),
                'Suffix' => null,
                'email' => $order->getCustomerEmail(),
                'ship_address' => $shipAddress->getData(),
                'bill_address' => $billingAddress->getData()
            ];
        } else {
            $customerData = [
                'Title' => $order->getCustomerPrefix(),
                'Name' => $order->getCustomerName(),
                'CompanyName' => $billingAddress->getCompany(),
                'GivenName' => $order->getCustomerFirstname(),
                'MiddleName' => $order->getCustomerMiddlename(),
                'FamilyName' => $order->getCustomerLastname(),
                'DisplayName' => $order->getCustomerName(),
                'Suffix' => $order->getCustomerSuffix(),
                'email' => $order->getCustomerEmail(),
                'ship_address' => $shipAddress->getData(),
                'bill_address' => $billingAddress->getData()
            ];
        }
        return $customerData;
    }
    /**
     * getTaxDetailOnItem
     * @param array $orderTaxDetail
     * @param int $itemId
     */
    public function getTaxDetailOnItem($orderTaxDetail, $itemId)
    {
        if (!empty($orderTaxDetail)) {
            foreach ($orderTaxDetail as $itemTax) {
                if ($itemTax['item_id'] == $itemId) {
                    return ['tax_percent' => $itemTax['tax_percent'], 'title' => $itemTax['title']];
                }
            }
        }
        return false;
    }
}
