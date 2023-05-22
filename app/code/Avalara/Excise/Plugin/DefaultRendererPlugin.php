<?php
namespace Avalara\Excise\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;

class DefaultRendererPlugin
{

    protected $CurrencyFactory;

    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $CurrencyFactory
    ) {
        $this->CurrencyFactory = $CurrencyFactory->create();
    }

    public function aroundGetColumnHtml(
        DefaultRenderer $defaultRenderer,
        \Closure $proceed,
        \Magento\Framework\DataObject $item,
        $column,
        $field = null
    ) {
        $html = '';
        if ($column == 'tax-amount') {
            $currency = $this->CurrencyFactory->load($item->getOrder()->getOrderCurrencyCode());
            $currencySymbol = $currency->getCurrencySymbol();
            $html = $currencySymbol.number_format((float)$item->getTaxAmount(), 2);
            if (!empty($item->getExciseTax())) {
                $html.= "<br/>Excise Tax - ".$currencySymbol.$item->getExciseTax();
            }
            if (!empty($item->getSalesTax())) {
                $html.= "<br/>Sales Tax - ".$currencySymbol.$item->getSalesTax();
            }
            $result = $html;
        } elseif ($column == 'tax-percent') {
            $orderTaxRate = 0;
            $itemTotal = $item->getPrice()*$item->getQtyOrdered();
            if (!empty($itemTotal)) {
                $orderTaxRate = number_format((float)(($item->getTaxAmount()*100) / $itemTotal), 2);
                $html = $orderTaxRate."%";
            }

            if (!empty($item->getExciseTax()) && !empty($itemTotal)) {
                $exciseTaxRate = number_format((float)(($item->getExciseTax()*100) / $itemTotal), 2);
                $html.= "<br/>Excise Tax - ".$exciseTaxRate."%";
            }
            if (!empty($item->getSalesTax()) && !empty($itemTotal)) {
                $salesTaxRate = number_format((float)(($item->getSalesTax()*100) / $itemTotal), 2);
                $html.= "<br/>Sales Tax - ".$salesTaxRate."%";
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
