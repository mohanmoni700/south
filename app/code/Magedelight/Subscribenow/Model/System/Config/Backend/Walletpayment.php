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

namespace Magedelight\Subscribenow\Model\System\Config\Backend;

class Walletpayment implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Return array of Store Wallet.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('No Wallet')],
            ['value' => 'magedelight_ewallet', 'label' => __('E-Wallet Payment')],
        ];
    }
}
