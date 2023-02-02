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

namespace Magedelight\Subscribenow\Block\Customer\Account\View;

use Magedelight\Subscribenow\Block\Customer\Account\View;

class Details extends View
{

    protected $_template = 'customer/account/view/details.phtml';

    /**
     * @return mixed
     */
    public function getSubscriptionStatus()
    {
        $status = $this->subscribeHelper->getStatusLabel();
        return $status[$this->getSubscription()->getSubscriptionStatus()];
    }

    /**
     * @return mixed
     */
    public function getQtyOrdered()
    {
        return (float) $this->getSubscription()->getQtySubscribed();
    }
    
    /**
     * Product Admin URL
     * @return string
     */
    public function getProductUrl()
    {
        if ($this->getSubscriptionProduct()) {
            return $this->getSubscriptionProduct()->getProductUrl();
        }
        return "#";
    }

    public function getProductOption()
    {
        $result = [];
        $productOptions = $this->getSubscription()->getAdditionalInfo('product_options');
        if ($productOptions) {
            foreach ($productOptions as $optionKey => $option) {
                if (in_array($optionKey, ['options', 'attributes_info'])) { // bundle_options
                    foreach ($option as $opt) {
                        $result[] = $opt;
                    }
                }
            }
        }

        return $result;
    }
}
