<?php

namespace Alfakher\RequestQuote\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class DisableLoyaltyCoupon
{

    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterPostCoupon(\Yotpo\Loyalty\Model\Api\Swell\Session\CouponManagement $subject, $result)
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getId()) {
            // Check if there are any quoted products in the cart
            $quotedProductsExist = $this->checkQuotedProductsExist($quote);
            if ($quotedProductsExist) {
                return $this->getErrorResponse(); // Return error response
            }
        }
        return $result;
    }

    private function checkQuotedProductsExist($quote)
    {
        if ($quote->getOptionByCode('amasty_quote_price')) {
            return false; // No quoted products
        }
    }

    private function getErrorResponse(): array
    {
        return [
            "error" => true,
            "message" => "Rewards cannot be applied to quoted products."
        ];
    }
}
