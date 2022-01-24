<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace HookahShisha\Avalara\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;

/**
 * Adminhtml sales order item renderer
 *
 * @api
 * @since 100.0.2
 */
class DefaultRendererPlugin
{

    /**
     * Checkout helper
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $CurrencyFactory;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $CurrencyFactory
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $CurrencyFactory
    ) {
        $this->CurrencyFactory = $CurrencyFactory->create();
    }

    /**
     * Retrieve rendered column html content
     *
     * @param DefaultRenderer $defaultRenderer
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject|Item $item
     * @param string $column
     * @param string $field
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 100.1.0
     */
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
            $html = $currencySymbol . number_format($item->getTaxAmount(), 2);
            if (!empty($item->getExciseTax())) {
                $html .= "<br/>Excise Tax - " . $currencySymbol . $item->getExciseTax();
            }
            if (!empty($item->getSalesTax())) {
                $html .= "<br/>Sales Tax - " . $currencySymbol . $item->getSalesTax();
            }
            $result = $html;
        } elseif ($column == 'tax-percent') {
            $itemTotal = (($item->getPrice() * $item->getQtyOrdered()) - $item->getDiscountAmount());
            if ($itemTotal) {
                $orderTaxRate = number_format(($item->getTaxAmount() * 100) / $itemTotal, 2);
            } else {
                $orderTaxRate = number_format(0, 2);
            }
            /*
             * Removed default condition
             * $orderTaxRate = number_format(($item->getTaxAmount()*100) / $itemTotal, 2);
             */
            $html = $orderTaxRate . "%";

            if (!empty($item->getExciseTax())) {
                $exciseTaxRate = number_format(($item->getExciseTax() * 100) / $itemTotal, 2);
                $html .= "<br/>Excise Tax - " . $exciseTaxRate . "%";
            }
            if (!empty($item->getSalesTax())) {
                $salesTaxRate = number_format(($item->getSalesTax() * 100) / $itemTotal, 2);
                $html .= "<br/>Sales Tax - " . $salesTaxRate . "%";
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
