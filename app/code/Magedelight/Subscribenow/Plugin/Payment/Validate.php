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

namespace Magedelight\Subscribenow\Plugin\Payment;

use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;

class Validate
{
    /**
     * @var SubscribeHelper
     */
    private $subscribeHelper;

    /**
     * @param SubscribeHelper $subscribeHelper
     */
    public function __construct(
        SubscribeHelper $subscribeHelper
    ) {
        $this->subscribeHelper = $subscribeHelper;
    }

    /**
     * @return bool
     */
    public function hasSubscriptionProduct()
    {
        return $this->subscribeHelper->hasSubscriptionProduct();
    }
}
