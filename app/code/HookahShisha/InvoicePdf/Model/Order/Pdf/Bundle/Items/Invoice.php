<?php
declare(strict_types=1);

namespace HookahShisha\InvoicePdf\Model\Order\Pdf\Bundle\Items;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Tax\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Invoice extends \Magento\Bundle\Model\Sales\Order\Pdf\Items\Invoice
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    private const CUSTOM_INVOICEPDF_STORES = [
        'ooka_uae_store_en',
        'ooka_uae_store_ar'
    ];

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param StringUtils $coreString
     * @param Json $serializer
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param ScopeConfigInterface $scopeConfig
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $taxData,
        Filesystem $filesystem,
        FilterManager $filterManager,
        StringUtils $coreString,
        Json $serializer,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $coreString,
            $serializer,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Draw bundle product item line
     *
     * @return void
     */
    public function draw()
    {
        $draw = $this->drawChildrenItems();
        $draw = $this->drawCustomOptions($draw);

        $page = $this->getPdf()->drawLineBlocks($this->getPage(), $draw, ['table_header' => true]);

        $this->setPage($page);
    }

    /**
     * Draw bundle product children items
     *
     * @return array
     */
    private function drawChildrenItems(): array
    {
        $this->_setFontRegular();

        $storeId = $this->_storeManager->getStore()->getId();
        $storeCode = $this->getStoreCodeById($storeId);

        $prevOptionId = '';
        $drawItems = [];
        $optionId = 0;
        $lines = [];
        foreach ($this->getChildren($this->getItem()) as $childItem) {
            $index = array_key_last($lines) !== null ? array_key_last($lines) + 1 : 0;
            $attributes = $this->getSelectionAttributes($childItem);
            if (is_array($attributes)) {
                $optionId = $attributes['option_id'];
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = ['lines' => [], 'height' => 15];
            }

            $stopExecutation = ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) ? 1 : 0;

            if (!$stopExecutation && $childItem->getOrderItem()->getParentItem()
            && $prevOptionId != $attributes['option_id']
            ) {
                $lines[$index][] = [
                'font' => 'italic',
                'text' => $this->string->split($attributes['option_label'], 45, true, true),
                'feed' => 35,
                ];

                $index++;
                $prevOptionId = $attributes['option_id'];
            }

            /* in case Product name is longer than 80 chars - it is written in a few lines */
            if ($childItem->getOrderItem()->getParentItem()) {
                $feed = ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) ? 35 : 40;
                $name = $this->getValueHtml($childItem);
            } else {
                $feed = 35;
                $name = $childItem->getName();
            }

            $ookaWhiteDetails = $this->_scopeConfig->getValue(
                "pdfinvoice_settings/general_setting/ooka_white_value",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $whiteDeviceAttr = json_decode($ookaWhiteDetails, true);

            $currCode = $this->_scopeConfig->getValue(
                "pdfinvoice_settings/general_setting/currency_label",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $ookaBlackDetails = $this->_scopeConfig->getValue(
                "pdfinvoice_settings/general_setting/ooka_black_value",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $blackDeviceAttr = json_decode($ookaBlackDetails, true);

            $ookaWhiteSku = $this->_scopeConfig->getValue(
                "pdfinvoice_settings/general_setting/ooka_white_sku",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $ookaBlackSku = $this->_scopeConfig->getValue(
                "pdfinvoice_settings/general_setting/ooka_black_sku",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $prodName = $this->string->split($name, 35, true, true);
            
            $lines[$index][] = ['text' => $prodName, 'feed' => $feed, 'font' => 'bold'];

            $orderedQty = __('Ordered ').$childItem->getQty() * 1;
            
            if ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {
                $qtyValue = ($index == 0) ? $orderedQty : ($childItem->getQty() * 1);
                $lines[$index][] = ['text' => $qtyValue, 'feed' => 280, 'align' => 'right'];
            }

            if ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {

                if ($childItem->getSku() == $ookaWhiteSku) {
                    foreach ($whiteDeviceAttr as $val) {
                        $j = $index++;
                        $j++;
                        $itemQty = $childItem->getQty() * 1;
                        $productName = $val['name'];
                        $unitPrice = $val['upi'];
                        $upWithSymbol = $currCode.number_format($unitPrice, 2, '.', '');
                        $totalAmt = $itemQty * $val['upi'];
                        $taWithSymbol = $currCode.number_format($totalAmt, 2, '.', '');
                        $prodName = $this->string->split($productName, 35, true, true);
                        $lines[$j][] = ['text' => $prodName,'feed' => $feed, 'font' => 'bold', 'align' => 'left'];

                        $lines[$j][] = ['text' => $itemQty,'feed' => 280, 'align' => 'right'];

                        $lines[$j][] = ['text' => $upWithSymbol,'feed' => 430, 'font' => 'bold', 'align' => 'right'];

                        $lines[$j][] = ['text' => $taWithSymbol,'feed' => 565, 'font' => 'bold', 'align' => 'right'];
                    }
                }

                if ($childItem->getSku() == $ookaBlackSku) {
            
                    foreach ($blackDeviceAttr as $val) {
                        $j = $index++;
                        $j++;
                        $itemQty = $childItem->getQty() * 1;
                        $productName = $val['name'];
                        $unitPrice = $val['upi'];
                        $upWithSymbol = $currCode.number_format($unitPrice, 2, '.', '');
                        $totalAmt = $itemQty * $val['upi'];
                        $taWithSymbol = $currCode.number_format($totalAmt, 2, '.', '');
                        $prodName = $this->string->split($productName, 35, true, true);
                        $lines[$j][] = ['text' => $prodName,'feed' => $feed, 'font' => 'bold', 'align' => 'left'];

                        $lines[$j][] = ['text' => $itemQty,'feed' => 280, 'align' => 'right'];

                        $lines[$j][] = ['text' => $upWithSymbol,'feed' => 430, 'font' => 'bold', 'align' => 'right'];

                        $lines[$j][] = ['text' => $taWithSymbol,'feed' => 565, 'font' => 'bold', 'align' => 'right'];
                    }
                }

                $lines = $this->drawPricesCustomPdf($childItem, $lines);
            } else {
                $lines = $this->drawSkus($childItem, $lines);
                $lines = $this->drawPrices($childItem, $lines);
            }
        }
        $drawItems[$optionId]['lines'] = $lines;

        return $drawItems;
    }

    /**
     * Draw sku parts
     *
     * @param DataObject $childItem
     * @param array $lines
     * @return array
     */
    private function drawSkus(DataObject $childItem, array $lines): array
    {
        $index = array_key_last($lines);
        if (!$childItem->getOrderItem()->getParentItem()) {
            $text = [];
            foreach ($this->string->split($this->getItem()->getSku(), 17) as $part) {
                $text[] = $part;
            }
            $lines[$index][] = ['text' => $text, 'feed' => 255];
        }

        return $lines;
    }

     /**
      * Draw prices for bundle product children items
      *
      * @param DataObject $childItem
      * @param array $lines
      * @return array
      */
    private function drawPrices(DataObject $childItem, array $lines): array
    {
        $index = array_key_last($lines);
        if ($this->canShowPriceInfo($childItem)) {
            $lines[$index][] = ['text' => $childItem->getQty() * 1, 'feed' => 435, 'align' => 'right'];

            $tax = $this->getOrder()->formatPriceTxt($childItem->getTaxAmount());
            $lines[$index][] = ['text' => $tax, 'feed' => 495, 'font' => 'bold', 'align' => 'right'];

            $item = $this->getItem();
            $this->_item = $childItem;
            $feedPrice = 380;
            $feedSubtotal = $feedPrice + 185;
            foreach ($this->getItemPricesForDisplay() as $priceData) {
                if (isset($priceData['label'])) {
                    // draw Price label
                    $lines[$index][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                    // draw Subtotal label
                    $lines[$index][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                    $index++;
                }
                // draw Price
                $lines[$index][] = [
                    'text' => $priceData['price'],
                    'feed' => $feedPrice,
                    'font' => 'bold',
                    'align' => 'right',
                ];
                // draw Subtotal
                $lines[$index][] = [
                    'text' => $priceData['subtotal'],
                    'feed' => $feedSubtotal,
                    'font' => 'bold',
                    'align' => 'right',
                ];
                $index++;
            }
            $this->_item = $item;
        }

        return $lines;
    }

    /**
     * Draw prices for bundle product children items
     *
     * @param DataObject $childItem
     * @param array $lines
     * @return array
     */
    private function drawPricesCustomPdf(DataObject $childItem, array $lines): array
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $ookaWhiteSku = $this->_scopeConfig->getValue(
            "pdfinvoice_settings/general_setting/ooka_white_sku",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $ookaBlackSku = $this->_scopeConfig->getValue(
            "pdfinvoice_settings/general_setting/ooka_black_sku",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $deviceSkus = $ookaWhiteSku ? trim($ookaWhiteSku).','.trim($ookaBlackSku) : trim($ookaBlackSku);

        $arrayDeviceSku = explode(',', $deviceSkus);

        $index = array_key_last($lines);
        if ($this->canShowPriceInfo($childItem)) {

            $item = $this->getItem();
            $this->_item = $childItem;
            $feedPrice = 430;
            $feedSubtotal = $feedPrice + 135;
            if (!in_array($this->getItem()->getSku(), $arrayDeviceSku)) {
                foreach ($this->getItemPricesForDisplay() as $priceData) {
                    if (isset($priceData['label'])) {
                        // draw Price label
                        $lines[$index][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'left'];
                        // draw Subtotal label
                        $lines[$index][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'left'];
                        $index++;
                    }
                    // draw Price
                    $lines[$index][] = [
                    'text' => $priceData['price'],
                    'feed' => $feedPrice,
                    'font' => 'bold',
                    'align' => 'right',
                    ];
                    // draw Subtotal
                    $lines[$index][] = [
                    'text' => $priceData['subtotal'],
                    'feed' => $feedSubtotal,
                    'font' => 'bold',
                    'align' => 'right',
                    ];

                    $index++;
                }
            }
            $this->_item = $item;
        }

        return $lines;
    }

    /**
     * Draw bundle product custom options
     *
     * @param array $draw
     * @return array
     */
    private function drawCustomOptions(array $draw): array
    {
        $options = $this->getItem()->getOrderItem()->getProductOptions();
        if ($options && isset($options['options'])) {
            foreach ($options['options'] as $option) {
                $lines = [];
                $lines[][] = [
                    'text' => $this->string->split(
                        $this->filterManager->stripTags($option['label']),
                        40,
                        true,
                        true
                    ),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                if ($option['value']) {
                    $text = [];
                    $printValue = $option['print_value'] ?? $this->filterManager->stripTags($option['value']);
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        foreach ($this->string->split($value, 30, true, true) as $subValue) {
                            $text[] = $subValue;
                        }
                    }

                    $lines[][] = ['text' => $text, 'feed' => 40];
                }

                $draw[] = ['lines' => $lines, 'height' => 15];
            }
        }

        return $draw;
    }

    /**
     * Retrieve Value HTML
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getValueHtml($item)
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $storeCode = $this->getStoreCodeById($storeId);

        $result = $this->filterManager->stripTags($item->getName());
        if (!$this->isShipmentSeparately($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                if ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {
                    $proName = $this->filterManager->stripTags($item->getName());
                    $result = $proName;
                } else {
                    $result = $this->filterManager->sprintf($attributes['qty'], ['format' => '%d']) . ' x ' . $result;
                }
            }
        }
        if (!$this->isChildCalculated($item)) {
            $attributes = $this->getSelectionAttributes($item);
            if ($attributes) {
                $result .= " " . $this->filterManager->stripTags(
                    $this->getOrderItem()->getOrder()->formatPrice($attributes['price'])
                );
            }
        }
        return $result;
    }

    /**
     * Get store code by store id
     *
     * @param  int $storeId
     */
    public function getStoreCodeById($storeId)
    {
        $store = $this->_storeManager->getStore($storeId);
        return $store->getCode();
    }

    /**
     * Returns enabled/disabled status.
     *
     * @return bool
     */
    public function isEnabled()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        
        return (bool) $this->_scopeConfig->getValue(
            "pdfinvoice_settings/general_setting/enabled",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
