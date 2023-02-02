<?php

/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Plugin;

use Magedelight\Subscribenow\Helper\Data;

class ProductListPlugin
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * Constructor
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param $subject
     * @param $result
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function afterGetProductPrice($subject, $result, \Magento\Catalog\Model\Product $product)
    {
        $subscriptionTextHTML = $this->helper->getSubscriptionListingText($product);
        
        if ($subscriptionTextHTML) {
            return $subscriptionTextHTML . $result;
        }

        return $result;
    }
}
