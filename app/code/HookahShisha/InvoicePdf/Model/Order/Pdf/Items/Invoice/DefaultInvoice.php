<?php
declare(strict_types=1);

namespace HookahShisha\InvoicePdf\Model\Order\Pdf\Items\Invoice;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\RtlTextHandler;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Tax\Helper\Data;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Model\ScopeInterface;

class DefaultInvoice extends \Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice
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
     * @param Context $context
     * @param Registry $registry
     * @param Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param StringUtils $string
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param ScopeConfigInterface $scopeConfig
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @param RtlTextHandler|null $rtlTextHandler
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $taxData,
        Filesystem $filesystem,
        FilterManager $filterManager,
        StringUtils $string,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ?RtlTextHandler $rtlTextHandler = null
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
            $string,
            $resource,
            $resourceCollection,
            $data
        );
        $this->rtlTextHandler = $rtlTextHandler ?: ObjectManager::getInstance()->get(RtlTextHandler::class);
    }

    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];
        $arrayDeviceSku = [];

        $storeId = $order->getStoreId();
        $storeCode = $this->getStoreCodeById($storeId);

        $enableCusPdf = $this->getPdfConfigData('enabled', $storeId);

        // draw Product name
        if ($enableCusPdf && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {
            $lines[0][] = [
                'text' => $this->string->split($this->prepareText((string) $item->getName()), 35, true, true),
                'feed' => 35,
                'font' => 'bold'
            ];

            // draw QTY
            $lines[0][] = ['text' => 'Ordered ' . $item->getQty() * 1, 'feed' => 280, 'align' => 'right'];

            $lines[0][] = ['text' => '', 'feed' => 290, 'align' => 'right'];

            $lines[0][] = ['text' => '', 'feed' => 400, 'align' => 'right'];

            $lines[0][] = ['text' => '', 'feed' => 550, 'align' => 'right'];

            $ookaWhiteDetails = $this->getPdfConfigData('ooka_white_value', $storeId);
            $whiteDeviceAttr = json_decode($ookaWhiteDetails, true);

            $currCode = $this->getPdfConfigData('currency_label', $storeId);

            $ookaBlackDetails = $this->getPdfConfigData('ooka_black_value', $storeId);
            $blackDeviceAttr = json_decode($ookaBlackDetails, true);

            $ookaWhiteSku = $this->getPdfConfigData('ooka_white_sku', $storeId);
            $ookaBlackSku = $this->getPdfConfigData('ooka_black_sku', $storeId);
            $deviceSkus = $ookaWhiteSku ? trim($ookaWhiteSku) . ',' . trim($ookaBlackSku) : trim($ookaBlackSku);
            $arrayDeviceSku = explode(',', $deviceSkus);

            if ($this->getSku($item) == $ookaWhiteSku) {
                foreach ($whiteDeviceAttr as $k => $val) {
                    $j = $k + 1;
                    $itemQty = $item->getQty() * 1;
                    $productName = $val['name'];
                    $unitPrice = $val['upi'];
                    $unitPriceSymbol = $currCode . number_format($unitPrice, 2, '.', '');
                    $totalAmt = $itemQty * $val['upi'];
                    $taWithSymbol = $currCode . number_format($totalAmt, 2, '.', '');
                    $prodName = $this->string->split($this->prepareText((string) $productName), 40, true, true);
                    $lines[$j][] = ['text' => $prodName, 'feed' => 35, 'font' => 'bold', 'align' => 'left'];

                    $lines[$j][] = ['text' => $itemQty, 'feed' => 280, 'align' => 'right'];

                    $lines[$j][] = ['text' => $unitPriceSymbol, 'feed' => 430, 'font' => 'bold', 'align' => 'right'];

                    $lines[$j][] = ['text' => $taWithSymbol, 'feed' => 565, 'font' => 'bold', 'align' => 'right'];

                }
            } elseif ($this->getSku($item) == $ookaBlackSku) {
                foreach ($blackDeviceAttr as $k => $val) {
                    $j = $k + 1;
                    $itemQty = $item->getQty() * 1;
                    $productName = $val['name'];
                    $unitPrice = $val['upi'];
                    $unitPriceSymbol = $currCode . number_format($unitPrice, 2, '.', '');
                    $totalAmt = $itemQty * $val['upi'];
                    $taWithSymbol = $currCode . number_format($totalAmt, 2, '.', '');
                    $prodName = $this->string->split($this->prepareText((string) $productName), 40, true, true);
                    $lines[$j][] = ['text' => $prodName, 'feed' => 35, 'font' => 'bold', 'align' => 'left'];

                    $lines[$j][] = ['text' => $itemQty, 'feed' => 280, 'align' => 'right'];

                    $lines[$j][] = ['text' => $unitPriceSymbol, 'feed' => 430, 'font' => 'bold', 'align' => 'right'];

                    $lines[$j][] = ['text' => $taWithSymbol, 'feed' => 565, 'font' => 'bold', 'align' => 'right'];

                }
            }

        } else {
            $lines[0][] = [
                'text' => $this->string->split($this->prepareText((string) $item->getName()), 35, true, true),
                'feed' => 35
            ];

            // draw SKU
            $lines[0][] = [
                'text' => $this->string->split($this->prepareText((string) $this->getSku($item)), 17),
                'feed' => 290,
                'align' => 'right',
            ];

            // draw QTY
            $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 435, 'align' => 'right'];
        }

        $stopExecutation = ($enableCusPdf && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) ? 1 : 0;

        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = ($enableCusPdf && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) ? 430 : 395;
        $feedPriceSubAddi = ($enableCusPdf && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) ? 135 : 170;
        $feedSubtotal = $feedPrice + $feedPriceSubAddi;

        if (!in_array($this->getSku($item), $arrayDeviceSku)) {
            foreach ($prices as $priceData) {
                if (isset($priceData['label'])) {
                    // draw Price label
                    $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                    // draw Subtotal label
                    $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                    $i++;
                }
                // draw Price
                $lines[$i][] = [
                    'text' => $priceData['price'],
                    'feed' => $feedPrice,
                    'font' => 'bold',
                    'align' => 'right',
                ];
                // draw Subtotal
                $lines[$i][] = [
                    'text' => $priceData['subtotal'],
                    'feed' => $feedSubtotal,
                    'font' => 'bold',
                    'align' => 'right',
                ];
                $i++;
            }
        }
        if (!$stopExecutation) {
            // draw Tax
            $lines[0][] = [
                'text' => $order->formatPriceTxt($item->getTaxAmount()),
                'feed' => 495,
                'font' => 'bold',
                'align' => 'right',
            ];
        }

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                // Checking whether option value is not null
                if ($option['value'] !== null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 40];
                    }
                }
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }

    /**
     * Returns prepared for PDF text, reversed in case of RTL text
     *
     * @param string $string
     * @return string
     */
    private function prepareText(string $string): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return $this->rtlTextHandler->reverseRtlText(html_entity_decode($string));
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
     * Get config data related to custom pdf.
     *
     * @param  string $field
     * @param  int $storeId
     */
    public function getPdfConfigData($field, $storeId)
    {
        $path = 'pdfinvoice_settings/general_setting/' . $field;
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    } //end getPdfConfigData()
}
