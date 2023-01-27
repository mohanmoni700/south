<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Block\View;

use Alfakher\SlopePayment\Helper\Config as SlopeConfig;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class SlopeWidgetJs extends Template
{
    /**
     * Slope config helper
     *
     * @var SlopeConfig
     */
    protected $slopeConfig;

    /**
     * Class Constructor
     *
     * @param Context $context
     * @param SlopeConfig $slopeConfig
     */
    public function __construct(Context $context, SlopeConfig $slopeConfig)
    {
        $this->slopeConfigHelper = $slopeConfig;
        parent::__construct($context);
    }

    /**
     * Get JS URL for slope widget on checkout page
     *
     * @return string
     */
    public function getJsUrl(): string
    {
        return $this->slopeConfigHelper->getJsSrcForCheckoutPage();
    }
}
