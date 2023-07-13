<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Plugin\Magento\Checkout\Block\Cart\Item;

class Renderer
{
    /**
     * remove showing subscription options on cart page because we are already showing subscription form
     */
    public function afterGetOptionList($subject, $result)
    {
        $optionsList = [];

        if ($result) {
            foreach ($result as $option) {
                $code = $option['code'] ?? false;

                if (!in_array($code, [
                    'billing_period_title',
                    'billing_cycle_title',
                    'init_amount',
                    'trial_amount',
                    'trial_period_title',
                    'trial_cycle_title',
                    'md_sub_start_date',
                    'md_sub_end_date'
                ])) {
                    $optionsList[] = $option;
                }
            }
        }
        
        return $optionsList;
    }
}
