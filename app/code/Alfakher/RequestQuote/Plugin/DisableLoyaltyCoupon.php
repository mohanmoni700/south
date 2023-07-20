<?php

declare(strict_types=1);

namespace Alfakher\RequestQuote\Plugin;

use Magento\Checkout\Model\Session;
use Yotpo\Loyalty\Helper\Data;

class DisableLoyaltyCoupon
{

    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @param Session $checkoutSession
     * @param Data $yotpoHelper
     */
    public function __construct(
        Session $checkoutSession,
        \Yotpo\Loyalty\Helper\Data $yotpoHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->yotpoHelper = $yotpoHelper;
    }

    /**
     * After post coupon method
     *
     * @param object $result
     * @return object
     */
    public function afterPostCoupon(object $result): object
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            if ($quote->getId() && $quote->getOptionByCode('amasty_quote_price')) {
                // Check if there are any quoted products in the cart
                $this->yotpoHelper->log("[Yotpo Loyalty API - Coupon - ERROR] " . 'Rewards cannot be applied to quoted products' . "\n", "error");
                $result['error'] = true;
                $result['error_message'] ='Rewards cannot be applied to quoted products.';
            }
        } catch (\Exception $e) {
            $this->yotpoHelper->log("[Yotpo Loyalty API - Coupon - ERROR] " . $e->getMessage() .
                "\n" . $e->getTraceAsString(), "error");
        }
        return $result;
    }
}
