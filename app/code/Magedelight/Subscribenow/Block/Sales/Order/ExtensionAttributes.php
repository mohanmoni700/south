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

namespace Magedelight\Subscribenow\Block\Sales\Order;

use Magedelight\Subscribenow\Model\Subscription;

/**
 * Add initial_amount && trial_amount block in adminhtml
 */
class ExtensionAttributes
{
    
    public function __construct(
        \Magedelight\Subscribenow\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * @param object $data
     * @return array
     */
    public function addInitAmount($data)
    {
        if (!$data->getSubscribenowInitAmount() || $data->getSubscribenowInitAmount() <= 0) {
            return [];
        }
        return new \Magento\Framework\DataObject(
            [
                'code' => Subscription::INIT_AMOUNT_FIELD_NAME,
                'value' => $data->getSubscribenowInitAmount(),
                'base_value' => $data->getBaseSubscribenowInitAmount(),
                'label' => $this->helper->getInitAmountTitle(),
            ]
        );
    }
    
    /**
     * @param object $data
     * @return array
     */
    public function addTrialAmount($data)
    {
        if (!$data->getSubscribenowTrialAmount() || $data->getSubscribenowTrialAmount() < 0) {
            return [];
        }
        return new \Magento\Framework\DataObject(
            [
                'code' => Subscription::TRIAL_AMOUNT_FIELD_NAME,
                'value' => $data->getSubscribenowTrialAmount(),
                'base_value' => $data->getBaseSubscribenowTrialAmount(),
                'label' => $this->helper->getTrialAmountTitle(),
            ]
        );
    }
}
