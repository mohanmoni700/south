<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Block\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Alfakher\SlopePayment\Helper\Config as SlopeConfig;

class SlopeWidgetJs extends Template
{

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
