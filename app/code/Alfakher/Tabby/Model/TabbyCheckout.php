<?php

declare(strict_types = 1);

namespace Alfakher\Tabby\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Tabby\Checkout\Exception\NotFoundException;
use Tabby\Checkout\Model\Method\Checkout;

class TabbyCheckout extends Checkout
{

    /**
     * Array to hold Tabby rejection reason code and message
     *
     * @var array|string[]
     */
    private array $rejectionReasons = [
        'not_available' =>
            'Sorry, Tabby is unable to approve this purchase. Please use an alternative payment method for your order.',
        'order_amount_too_high' =>
            'This purchase is above your current spending limit with Tabby, try a smaller cart or use another payment method',
        'order_amount_too_low' =>
            'The purchase amount is below the minimum amount required to use Tabby, try adding more items or use another payment method'
    ];

    protected $_codeTabby = 'installments';

    /**
     * @var Order
     */
    private $order;

    /**
     * @param  Order $order
     * @return void
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Returns tabby redirect URL
     *
     * @return string
     * @throws LocalizedException
     */
    public function getOrderRedirectUrl()
    {
        $requestData = [
            "lang"          => strstr($this->localeResolver->getLocale(), '_', true) == 'en' ? 'en' : 'ar',
            "merchant_code" => $this->order->getStore()->getCode() . ($this->getConfigData('local_currency') ? '_' . $this->order->getOrderCurrencyCode() : ''),
            "merchant_urls" => $this->getMerchantUrls(),
            "payment"       => $this->getSessionPaymentObject($this->order)
        ];

        $redirectUrl = $this->_urlInterface->getUrl('tabby/result/failure');

        try {
            $result = $this->_checkoutApi->createSession($this->order->getStoreId(), $requestData);

            if ($result && property_exists($result, 'status') && $result->status == 'created') {
                if (property_exists($result->configuration->available_products, $this->_codeTabby)) {
                    // register new payment id for order
                    $this->getInfoInstance()->setAdditionalInformation([
                        self::PAYMENT_ID_FIELD => $result->payment->id
                    ]);
                    $redirectUrl = $result->configuration->available_products->{$this->_codeTabby}[0]->web_url;
                } else {
                    throw new LocalizedException(__("Selected payment method not available."));
                }
            } else {
                if (property_exists($result->configuration->products->installments, 'rejection_reason')
                    && isset($this->rejectionReasons[$result->configuration->products->installments->rejection_reason])
                ) {
                    throw new LocalizedException(
                        __($this->rejectionReasons[$result->configuration->products->installments->rejection_reason]),
                        null,
                        100
                    );
                } else {
                    throw new LocalizedException(__("Response not have status field or payment rejected"));
                }
            }
        } catch (\Exception $e) {
            $this->_ddlog->log("error", "createSession exception", $e, $requestData);
            if ($e->getCode() === 100) {
                throw $e;
            } else {
                throw new LocalizedException(__("Something went wrong. Please try again later or contact support."));
            }
        }

        return $redirectUrl;
    }

    /**
     * @inheritDoc
     */
    public function getInfoInstance()
    {
        $instance = $this->order->getPayment();

        if (!$instance instanceof InfoInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot retrieve the payment information object instance.')
            );
        }

        return $instance;
    }

    /**
     * Get payment object from quote
     *
     * @param  Quote $quote
     * @return InfoInterface
     * @throws LocalizedException
     */
    public function getInfoInstanceFromQuote(Quote $quote): InfoInterface
    {
        $instance = $quote->getPayment();

        if (!$instance instanceof InfoInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot retrieve the payment information object instance.')
            );
        }

        return $instance;
    }

    /**
     * Tabby Pre-scoring api call
     *
     * @param  Quote $quote
     * @return array
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function tabbyCreateSession(Quote $quote): array
    {

        $requestData = [
            'lang' => strstr($this->localeResolver->getLocale(), '_', true) == 'en' ? 'en' : 'ar',
            'merchant_code' => $quote->getStore()->getCode() . ($this->getConfigData('local_currency') ? '_' . $quote->getQuoteCurrencyCode() : ''),
            'merchant_urls' => $this->getMerchantUrls(),
            'payment' => $this->getSessionPaymentObjectFromQuote($quote)
        ];

        $result = $this->_checkoutApi->createSession($quote->getStoreId(), $requestData);

        if ($result && property_exists($result, 'status') && $result->status == 'created') {
            if (property_exists($result->configuration->available_products, $this->_codeTabby)) {
                return [
                    'is_available' => true,
                    'rejection_message' => null
                ];
            } else {
                return [
                    'is_available' => false,
                    'rejection_message' => __($this->rejectionReasons['not_available'])
                ];
            }
        } else {
            if (property_exists($result->configuration->products->installments, 'rejection_reason')
                && isset($this->rejectionReasons[$result->configuration->products->installments->rejection_reason])
            ) {
                return [
                    'is_available' => false,
                    'rejection_message' => __($this->rejectionReasons[$result->configuration->products->installments->rejection_reason])
                ];
            } else {
                return [
                    'is_available' => false,
                    'rejection_message' => __($this->rejectionReasons['not_available'])
                ];
            }
        }
    }

    /**
     * Get payment data from quote
     *
     * @param  Quote $quote
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getSessionPaymentObjectFromQuote(Quote $quote): array
    {
        $address = $quote->getShippingAddress() ?: $quote->getBillingAddress();
        $customer = $quote->getCustomer();

        if (!$quote->getCustomerIsGuest()) {
            $customer = $this->customerRepository->getById($quote->getCustomerId());
        }

        $orderHistory = $this->orderHistory->getOrderHistoryObject($customer, $quote->getCustomerEmail(), $address ? $address->getTelephone() : null);

        return [
            "amount"    => strval($this->getTabbyPriceFromQuote($quote, 'grand_total')),
            "currency"  => $this->getIsInLocalCurrencyFromQuote($quote) ? $quote->getQuoteCurrencyCode() : $quote->getBaseCurrencyCode(),
            "buyer"     => [
                "phone"     => $address ? $address->getTelephone() : '',
                "email"     => $quote->getCustomerEmail(),
                "name"      => $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname()
            ],
            "shipping_address" => [
                "city"      => $address ? $address->getCity() : '',
                "address"   => $address ? implode(PHP_EOL, $address->getStreet()) : '',
                "zip"       => $address ? $address->getPostcode() : ''
            ],
            "order"     => [
                "tax_amount"        => strval($this->getTabbyPriceFromQuote($quote, 'tax_amount')),
                "shipping_amount"   => strval($this->getTabbyPriceFromQuote($quote, 'shipping_amount')),
                "discount_amount"   => strval($this->getTabbyPriceFromQuote($quote, 'discount_amount')),
                "items"             => $this->getSessionQuoteItems($quote)
            ],
            "buyer_history"     => $this->buyerHistory->getBuyerHistoryObject($customer, $orderHistory),
            "order_history"     => $this->orderHistory->limitOrderHistoryObject($orderHistory)
        ];
    }

    /**
     * Get quote items
     *
     * @param Quote $quote
     * @return array
     */
    private function getSessionQuoteItems($quote)
    {
        $items = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $items[] = [
                'title'         => $item->getName(),
                'description'   => $item->getDescription(),
                'quantity'      => $item->getQty(),
                'unit_price'    => strval(round($item->getPrice() - $item->getDiscountAmount() + $item->getTaxAmount(), 2)),
                'tax_amount'    => strval(round($item->getTaxAmount(), 2)),
                'reference_id'  => $item->getSku(),
                'image_url'     => $this->getSessionItemImageUrl($item),
                'product_url'   => $item->getProduct()->getUrlInStore(),
                'category'      => $this->getSessionCategoryName($item)
            ];
        }
        return $items;
    }

    /**
     * Get tabby price
     *
     * @param  Quote $quote
     * @param  string $field
     * @return int|float
     * @throws LocalizedException
     */
    public function getTabbyPriceFromQuote(Quote $quote, string $field)
    {
        return round($this->getIsInLocalCurrencyFromQuote($quote) ? $quote->getData($field) : $quote->getData('base_' . $field), 2);
    }

    /**
     * @param  Quote $quote
     * @return bool
     * @throws LocalizedException
     */
    protected function getIsInLocalCurrencyFromQuote(Quote $quote): bool
    {
        return ($this->getInfoInstanceFromQuote($quote)->getAdditionalInformation(self::TABBY_CURRENCY_FIELD) == 'order');
    }
}
