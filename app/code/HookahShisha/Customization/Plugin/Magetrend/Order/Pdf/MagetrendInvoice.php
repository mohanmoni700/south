<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace HookahShisha\Customization\Plugin\Magetrend\Order\Pdf;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magetrend\PdfTemplates\Helper\Data;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magetrend\PdfTemplates\Model\Template;
use Magento\Framework\Registry;

/**
 * Invoice PDF plugin class
 * override the magetrend Invoice PDF plugin
 * plugin Name: magetrend-invoice-pdf
 */
class MagetrendInvoice
{
    const TEMPLATE_ID_CONFIG_PATH = "invoice_template_customization/forced_invoice_template/template_id";
    const KN_REGION_ID_CONFIG_PATH = "invoice_template_customization/region_id/kn_region_id";
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Data
     */
    public $moduleHelper;

    /**
     * @var Template
     */
    public $pdfTemplate;

    /**
     * @var Registry
     */
    public $registry;

    /**
     * @param Data $moduleHelper
     * @param Template $pdfTemplate
     * @param Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Data $moduleHelper,
        Template $pdfTemplate,
        Registry $registry,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->pdfTemplate = $pdfTemplate;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Replace invoice renderer
     *
     * @param Invoice $invoicePdfModel
     * @param callable $proceed
     * @param array $invoices
     * @return \Zend_Pdf
     */
    public function aroundGetPdf(Invoice $invoicePdfModel, callable $proceed, $invoices = []) // NOSONAR
    {
        $storeId = $this->getStoreId($invoices);
        if (!$this->moduleHelper->isActive($storeId)
            || $this->registry->registry(Data::REGISTRY_IGNORE_KEY)) {
            return $proceed($invoices);
        }
        /** JIRA ID: B2BHW-1421 Show tax column for KY shipping state code */
        $templateId = $this->getInvoiceTemplateId($storeId);
        $showTaxColumn = $this->canShowTaxColumn($invoices);
        if (!empty($templateId) && $showTaxColumn) {
            return $this->pdfTemplate->getPdf($invoices, $templateId);
        }
        /** Customization end */
        return $this->pdfTemplate->getPdf($invoices);
    }

    /**
     * Returns order store id
     *
     * @param $invoices
     * @return int
     */
    public function getStoreId($invoices)
    {
        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                return $invoice->getStoreId();
            }
        }
        return 0;
    }

    /**
     * get Forced Invoice template id for KY state
     * @param $store
     * @return mixed
     */
    public function getInvoiceTemplateId($store = null)
    {
        return $this->scopeConfig->getValue(
            self::TEMPLATE_ID_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * get region id for Kentucky state
     * @param $store
     * @return mixed
     */
    public function getKentuckyRegionId($store = null)
    {
        return $this->scopeConfig->getValue(
            self::KN_REGION_ID_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * can Show tax column for this invoice
     * @param $invoices
     * @return mixed
     */
    public function canShowTaxColumn($invoices)
    {
        $storeId = $this->getStoreId($invoices);
        $kentuckyStateId = $this->getKentuckyRegionId($storeId);
        foreach ($invoices as $invoice) {
            $address = $invoice->getOrder()->getShippingAddress();
            if ($address->getRegionId() == $kentuckyStateId) {
                return true;
            }
        }
        return false;
    }
}
