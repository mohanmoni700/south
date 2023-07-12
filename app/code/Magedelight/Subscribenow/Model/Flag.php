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

class Flag extends \Magento\Framework\Flag
{

    const REPORT_PRODUCT_SUBSCRIPTION_FLAG_CODE = 'report_subscription_product_aggregated';
    const REPORT_CUSTOMER_SUBSCRIPTION_FLAG_CODE = 'report_subscription_customer_aggregated';

    /**
     * Setter for flag code.
     *
     * @codeCoverageIgnore
     *
     * @param string $code
     *
     * @return $this
     */
    public function setReportFlagCode($code)
    {
        $this->_flagCode = $code;

        return $this;
    }
}
