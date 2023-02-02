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
namespace Magedelight\Subscribenow\Block\Customer\Wishlist\Item\Column;

use Magento\Framework\App\Http\Context;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Column as WishlistColumns;
use Magento\Catalog\Block\Product\Context as ProductContext;
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;

class SubscriptionText extends WishlistColumns
{
    
    /**
     * Subscription Helper
     */
    private $subscriptionHelper;
    
    /**
     * @param ProductContext $context
     * @param SubscriptionHelper $subscriptionHelper
     * @param array $data
     */
    public function __construct(
        Context $httpContext,
        ProductContext $context,
        SubscriptionHelper $subscriptionHelper,
        array $data = []
    ) {
        $this->subscriptionHelper = $subscriptionHelper;
        parent::__construct(
            $context,
            $httpContext,
            $data
        );
    }
    
    public function getSubscriptionText()
    {
        $item = $this->getItem();
        $product = $item->getProduct();
        
        return $this->subscriptionHelper->getSubscriptionListingText($product);
    }
}
