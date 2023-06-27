<?php

namespace Alfakher\RequestQuote\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yotpo\Loyalty\Model\Api\Swell\Session\CouponManagement;

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
     * After plugin to throws error message if Rewards are applied for quotes products
     *
     * @param CouponManagement $subject
     * @param $result
     * @return array|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterPostCoupon(CouponManagement $subject, $result)
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

    /**
     * Check if the cart contains quoted products
     *
     * @param $quote
     * @return bool
     */
    private function checkQuotedProductsExist($quote): bool
    {
        if ($quote->getOptionByCode('amasty_quote_price')) {
            return false;
        }
        return true;
    }

    /**
     * Show error message if customer tries to add reward point for quoted cart
     *
     * @return array
     */
    private function getErrorResponse(): array
    {
        return [
            "error" => true,
            "message" => "Rewards cannot be applied to quoted products."
        ];
    }
}
