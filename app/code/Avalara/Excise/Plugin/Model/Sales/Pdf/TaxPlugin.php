<?php

namespace Avalara\Excise\Plugin\Model\Sales\Pdf;

use Magento\Framework\Locale\FormatInterface;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Pdf\Tax;
use Avalara\Excise\Helper\Config as AvaTaxHelperConfig;

/**
 * Class TaxPlugin
 *
 * @package Avalara\Excise\Plugin\Model\Sales\Pdf
 */
class TaxPlugin
{
    const TOTAL_TAX_LABEL = 'Total Tax';

    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * @var Config
     */
    private $taxConfig;

    /**
     * @var AvaTaxHelperConfig
     */
    private $avaTaxHelperConfig;

    /**
     * TaxPlugin constructor.
     *
     * @param FormatInterface $format
     * @param Config $taxConfig
     * @param AvaTaxHelperConfig $avaTaxHelperConfig
     */
    public function __construct(
        FormatInterface $format,
        Config $taxConfig,
        AvaTaxHelperConfig $avaTaxHelperConfig
    ) {
        $this->format = $format;
        $this->taxConfig = $taxConfig;
        $this->avaTaxHelperConfig = $avaTaxHelperConfig;
    }

    /**
     * @param Tax $subject
     * @param $totals
     * @return array
     */
    public function afterGetTotalsForDisplay(Tax $subject, $totals)
    {
        if (empty($totals)) {
            return $totals;
        }

        $totalTaxKey = array_search($this->getTotalTaxLabel($subject->getTitle(), $subject),
            array_column($totals, 'label'));
        $totalTax = $totals[$totalTaxKey];
        unset($totals[$totalTaxKey]);


        $store = $subject->getOrder()->getStore();
        $taxTitle = self::TOTAL_TAX_LABEL;
        $taxIncluded = $this->avaTaxHelperConfig->getTaxSummaryConfig();
        if ($taxIncluded)
            $taxTitle .= AvaTaxHelperConfig::XML_SUFFIX_AVATAX_TAX_INCLUDED;

        if ($this->taxConfig->displaySalesFullSummary($store)) {
            $totalTax['label'] = $this->getTotalTaxLabel($taxTitle, $subject);
            $result = array_merge([$totalTax], $totals);
        }else{
            $totalTax['label'] = $this->getTotalTaxLabel($taxTitle, $subject);
            $result = [$totalTax];
        }

        return $result;
    }

    /**
     * @param $title
     * @param $subject
     * @return string
     */
    public function getTotalTaxLabel($title, $subject)
    {
        $title = __($title);
        if ($subject->getTitleSourceField()) {
            $label = $title . $this->getTitleDescription() . ':';
        } else {
            $label = $title . ':';
        }

        return $label;
    }
}
