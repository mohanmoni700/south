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
        if ($column == 'tax-amount') {
            $currency = $this->CurrencyFactory->load($item->getOrder()->getOrderCurrencyCode());
            $currencySymbol = $currency->getCurrencySymbol();
        
            $html = $currencySymbol.number_format($item->getTaxAmount(), 2);
            if (!empty($item->getExciseTax())) {
                $html.= "<br/>Excise Tax - ".$currencySymbol.$item->getExciseTax();
            }
            if (!empty($item->getSalesTax())) {
                $html.= "<br/>Sales Tax - ".$currencySymbol.$item->getSalesTax();
            }
            $result = $html;
        } elseif ($column == 'tax-percent') {
            $itemTotal = (($item->getPrice()*$item->getQtyOrdered())-$item->getDiscountAmount());
            $orderTaxRate = number_format(($item->getTaxAmount()*100) / $itemTotal, 2);
            $html = $orderTaxRate."%";

            if (!empty($item->getExciseTax())) {
                $exciseTaxRate = number_format(($item->getExciseTax()*100) / $itemTotal, 2);
                $html.= "<br/>Excise Tax - ".$exciseTaxRate."%";
            }
            if (!empty($item->getSalesTax())) {
                $salesTaxRate = number_format(($item->getSalesTax()*100) / $itemTotal, 2);
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
