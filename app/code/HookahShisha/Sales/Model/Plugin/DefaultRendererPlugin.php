<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Quote
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Sales\Model\Plugin;

use Closure;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\DataObject;
use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;

class DefaultRendererPlugin
{
    /**
     * @var Currency
     */
    protected Currency $CurrencyFactory;

    /**
     * @param CurrencyFactory $CurrencyFactory
     */
    public function __construct(
        CurrencyFactory $CurrencyFactory
    ) {
        $this->CurrencyFactory = $CurrencyFactory->create();
    }

    /**
     * Adjusted Avalar_Excise plugin
     *
     * @param DefaultRenderer $defaultRenderer
     * @param Closure $proceed
     * @param DataObject $item
     * @param string $column
     * @param string|null $field
     * @return mixed|string
     */
    public function aroundGetColumnHtml(   // NOSONAR
        DefaultRenderer $defaultRenderer, // NOSONAR
        Closure $proceed,
        DataObject $item,
        string $column,
        string $field = null
    ) {
        if ($column == 'tax-amount') {
            $currency = $this->CurrencyFactory->load($item->getOrder()->getOrderCurrencyCode());
            $currencySymbol = $currency->getCurrencySymbol();
            $html = $currencySymbol . number_format($item->getTaxAmount(), 2);

            if (!empty($item->getExciseTax())) {
                $html .= "<br/>Excise Tax - " . $currencySymbol . $item->getExciseTax();
            }

            if (!empty($item->getSalesTax())) {
                $html .= "<br/>Sales Tax - " . $currencySymbol . $item->getSalesTax();
            }

            $result = $html;
        } elseif ($column == 'tax-percent') {
            $html = $defaultRenderer->displayTaxPercent($item);

            if (!empty($item->getExciseTax())) {
                $html .= "<br/>Excise Tax - " . $defaultRenderer->displayTaxPercent($item);
            }

            if (!empty($item->getSalesTax())) {
                $html .= "<br/>Sales Tax - " . $defaultRenderer->displayTaxPercent($item);
            }

            $result = $html;
        } else {
            if ($field) {
                $result = $proceed($item, $column, $field);
            } else {
                $result = $proceed($item, $column);
            }
        }

        return $result;
    }
}
