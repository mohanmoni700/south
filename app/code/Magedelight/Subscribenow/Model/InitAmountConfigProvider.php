<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magedelight\Subscribenow\Helper\Data as DataHelper;
use Magedelight\Subscribenow\Model\Service\CalculationService;

/**
 * Class InitAmountConfigProvider
 * @package Magedelight\Subscribenow\Model
 */
class InitAmountConfigProvider implements ConfigProviderInterface
{
    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @param DataHelper $helper
     * @param CalculationService $calculator
     */
    public function __construct(DataHelper $helper, CalculationService $calculator)
    {
        $this->helper = $helper;
        $this->calculator = $calculator;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $initAmountConfig = [];
        if (!$this->helper->isModuleEnable()) {
            return $initAmountConfig;
        }
        
        $quote = $this->helper->getCurrentQuote();
        $fees = $this->calculator->calculate($quote, 'init_amount');

        $initAmountConfig[Subscription::INIT_AMOUNT_FIELD_NAME.'_title'] = $this->helper->getInitAmountTitle();
        $initAmountConfig[Subscription::INIT_AMOUNT_FIELD_NAME] = $fees->getAmount();
        $initAmountConfig['show_hide_'.Subscription::INIT_AMOUNT_FIELD_NAME] = $fees->getAmount() > 0.0;
        return $initAmountConfig;
    }
}
