<?php
declare(strict_types=1);

namespace HookahShisha\InvoicePdf\Model\Order\Pdf;

use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\RtlTextHandler;
use Magento\Framework\App\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\App\Emulation as AppEmulationNew;

class Invoice extends \Magento\Sales\Model\Order\Pdf\Invoice
{
    /**
     * @var AppEmulationNew
     */
    protected $appEmulationNew;

    /**
     * @var RtlTextHandler
     */
    private $rtlTextHandler;

    /**
     * @param Data $paymentData
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param Config $pdfConfig
     * @param Factory $pdfTotalFactory
     * @param ItemsFactory $pdfItemsFactory
     * @param TimezoneInterface $localeDate
     * @param StateInterface $inlineTranslation
     * @param Renderer $addressRenderer
     * @param StoreManagerInterface $storeManager
     * @param Emulation $appEmulation
     * @param AppEmulationNew $appEmulationNew
     * @param array $data
     * @param RtlTextHandler|null $rtlTextHandler
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Data $paymentData,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        Config $pdfConfig,
        Factory $pdfTotalFactory,
        ItemsFactory $pdfItemsFactory,
        TimezoneInterface $localeDate,
        StateInterface $inlineTranslation,
        Renderer $addressRenderer,
        StoreManagerInterface $storeManager,
        Emulation $appEmulation,
        AppEmulationNew $appEmulationNew,
        array $data = [],
        ?RtlTextHandler $rtlTextHandler = null
    ) {
        $this->appEmulationNew = $appEmulationNew;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $appEmulation,
            $data
        );
        $this->rtlTextHandler = $rtlTextHandler ?: ObjectManager::getInstance()->get(RtlTextHandler::class);
    }

    private const CUSTOM_INVOICEPDF_STORES = [
        'ooka_uae_store_en',
        'ooka_uae_store_ar'
    ];

    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $storeCode = $this->getStoreCodeById($storeId);
        /* Add table head */
        $this->_setFontRegular($page, 10);
        
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        if ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {
            $page->drawRectangle(25, $this->y - 5, 570, $this->y - 35);
            $this->y -= 18;
        } else {
            $page->drawRectangle(25, $this->y, 570, $this->y - 15);
            $this->y -= 10;
        }
            
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        if ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {

            $upColumn = $this->string->split('Unit Price incl.(incl. VAT)', 16, false, false);
            $taColumn = $this->string->split('Total Amount(incl. VAT)', 12, false, false);

            $lines[0][] = ['text' => __('Item Description'), 'feed' => 35, 'align' => 'left'];
            $lines[0][] = ['text' => __('QTY(Pcs)'), 'feed' => 250, 'align' => 'left'];

            $lines[0][] = ['text' => $upColumn, 'feed' => 370, 'align' => 'left', 'line-'];
            $lines[0][] = ['text' => $taColumn, 'feed' => 560, 'align' => 'right'];
            $lineBlock = ['lines' => $lines, 'height' => 10];
        } else {
            $lines[0][] = ['text' => __('Products'), 'feed' => 35];

            $lines[0][] = ['text' => __('SKU'), 'feed' => 290, 'align' => 'right'];

            $lines[0][] = ['text' => __('Qty'), 'feed' => 435, 'align' => 'right'];

            $lines[0][] = ['text' => __('Price'), 'feed' => 360, 'align' => 'right'];

            $lines[0][] = ['text' => __('Tax'), 'feed' => 495, 'align' => 'right'];

            $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

            $lineBlock = ['lines' => $lines, 'height' => 5];
        }

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param array|Collection $invoices
     * @return \Zend_Pdf
     */
    public function getPdf($invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                $this->appEmulationNew->startEnvironmentEmulation(
                    $invoice->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            $storeCode = $this->getStoreCodeById($invoice->getStoreId());
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            if ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {
                $this->insertHeading($page);
            }

            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                $this->appEmulationNew->stopEnvironmentEmulation();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Insert heading to pdf page
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function insertHeading(&$page)
    {
        $this->_setFontBold($page, 15);
        $top = 815;
        $companyName = __('Tax Invoice');
        $page->drawText($companyName, 250, $top-80, 'UTF-8');
        $this->_setFontRegular($page, 10);
    }

    /**
     * Insert order to pdf page.
     *
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $storeCode = $this->getStoreCodeById($storeId);

        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;
        
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        if ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {
            $page->drawRectangle(25, $top - 30, 570, $top - 75);
            $this->setDocHeaderCoordinates([25, $top-30, 570, $top - 75]);
        } else {
            $page->drawRectangle(25, $top, 570, $top - 55);
            $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        }
        
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $top -= ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) ? 56 : 30;
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top, 'UTF-8');
            $top +=15;
        }
        
        $top -= ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) ? 26 : 30;

        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top,
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $this->rtlTextHandler->reverseRtlText($_value);
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $this->rtlTextHandler->reverseRtlText($_value);
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "("
                . __('Total Shipping Charges')
                . " "
                . $order->formatPriceTxt($order->getShippingAmount())
                . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    /**
     * Insert totals to pdf page
     *
     * @param  \Zend_Pdf_Page $page
     * @param  \Magento\Sales\Model\AbstractModel $source
     * @return \Zend_Pdf_Page
     */
    protected function insertTotals($page, $source)
    {
        $order = $source->getOrder();

        $storeId = $this->_storeManager->getStore()->getId();

        $storeCode = $this->getStoreCodeById($storeId);

        if ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {
            $vatPer = $this->_scopeConfig->getValue(
                "pdfinvoice_settings/general_setting/vat_tax_per",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $vatDivideBy = $this->_scopeConfig->getValue(
                "pdfinvoice_settings/general_setting/vat_divide_by",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $grandTo = $order->getGrandTotal();
            $vat = $order->formatPriceTxt($grandTo * ($vatPer/$vatDivideBy));
        }
        $totals = $this->_getTotalsList();
        $lineBlock = ['lines' => [], 'height' => 15];
        foreach ($totals as $total) {
            $stopExecutation = $this->isEnabled() &&
            in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES) &&
            isset($total['title_source_field']) &&
            $total['title_source_field'] == 'discount_description';

            if (!$stopExecutation) {
                $total->setOrder($order)->setSource($source);

                if ($total->canDisplay()) {
                    $total->setFontSize(10);
                    foreach ($total->getTotalsForDisplay() as $totalData) {
                        $lineBlock['lines'][] = [
                            [
                                'text' => $totalData['label'],
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => $totalData['amount'],
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ],
                        ];
                    }
                }
            }
        }
        if ($this->isEnabled() && in_array($storeCode, self::CUSTOM_INVOICEPDF_STORES)) {

            $order = $source->getOrder();
            $grandTo = $this->digitToWords($order->getGrandTotal());

            $lineBlock['lines'][] = [
                        [
                            'text' => 'VAT @ 5%',
                            'feed' => 475,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold',
                        ],
                        [
                            'text' => $vat,
                            'feed' => 565,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold'
                        ],
                    ];

            $lineBlock['lines'][] = [
                        [
                            'text' => 'GRAND TOTAL',
                            'feed' => 25,
                            'align' => 'left',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold',
                        ],
                        [
                            'text' => ucwords($grandTo),
                            'feed' => 565,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold'
                        ],
                    ];
        }

        $this->y -= 20;
        $page = $this->drawLineBlocks($page, [$lineBlock]);
        return $page;
    }

    /**
     * Convert digit to words
     *
     * @param  int $number
     * @return string
     */
    public function digitToWords($number)
    {
        $ntoword = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $spellout = str_replace('-', ' ', $ntoword->format($number));
        if (str_contains($spellout, 'hundred') && str_contains($spellout, 'point')) {
            $spellout = str_replace("point", "and", $spellout).' Fils';
        } elseif (str_contains($spellout, 'hundred')) {
            $spellout = str_replace("hundred", "hundred and", $spellout);
        }

        return $spellout;
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
