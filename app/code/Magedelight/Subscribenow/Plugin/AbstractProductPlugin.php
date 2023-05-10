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
use Magedelight\Subscribenow\Model\Subscription;

/**
 * Retrieve url for add product to cart
 * Will return product view page URL if product has required options
 */
class AbstractProductPlugin
{

    /**
     * @var Data
     */
    private $helper;
    
    /**
     * @var Subscription
     */
    private $subscription;

    public function __construct(
        Data $helper,
        Subscription $subscription
    ) {
        $this->subscription = $subscription;
        $this->helper = $helper;
    }

    /**
     * @param object $subject
     * @param string $result
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function afterGetAddToCartUrl($subject, $result, $product, $additional = [])
    {
        if (!$this->helper->isModuleEnable()) {
            return $result;
        }
        
        $isValid = $this->subscription->isValidBuyFromList($product);
        if (!$isValid) {
            return $product->getProductUrl();
        }
        return $result;
    }
}
