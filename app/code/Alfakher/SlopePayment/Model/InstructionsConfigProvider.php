<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Model;

use Alfakher\SlopePayment\Model\Payment\SlopePayment;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;

class InstructionsConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        SlopePayment::PAYMENT_METHOD_SLOPEPAYMENT_CODE,
    ];

    /**
     * @var AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteItemRepository
     */
    private $quoteItemRepository;

    /**
     * @var SlopeConfigHelper
     */
    protected $slopeConfig;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param CheckoutSession $checkoutSession
     * @param QuoteItemRepository $quoteItemRepository
     * @param SlopeConfigHelper $slopeConfig
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        CheckoutSession $checkoutSession,
        QuoteItemRepository $quoteItemRepository,
        SlopeConfigHelper $slopeConfig
    ) {
        $this->escaper = $escaper;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->config = $slopeConfig;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $quote = $this->checkoutSession->getQuote();
        $billingAddress = $quote->getBillingAddress();

        $address =
            [
            "line1" => $billingAddress->getStreet()[0],
            "city" => $billingAddress->getCity(),
            "state" => $billingAddress->getRegionCode(),
            "postalCode" => $billingAddress->getPostcode(),
            "country" => $billingAddress->getCountry(),
        ];

        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $billPhone = $billingAddress->getTelephone();
                $config['payment']['instructions'][$code] = $this->getInstructions($code);
                $config['slope']['customer']['email'] = $quote->getCustomerEmail();
                $config['slope']['customer']['phone'] =
                $this->config->getSlopeFormattedPhone($billPhone);
                $config['slope']['customer']['businessName'] = $billingAddress->getCompany() ?: 'NA';
                $config['slope']['customer']['address'] = $address;
                $config['slope']['customer']['externalId'] = $quote->getCustomerId();
                $config['slope']['order']['total'] = $quote->getGrandTotal() * 100;
                $config['slope']['order']['currency'] = strtolower($quote->getQuoteCurrencyCode());
                $config['slope']['order']['billingAddress'] = $address;
                $config['slope']['order']['externalId'] = $quote->getId();
                $config['slope']['order']['items'] = $this->getQuoteItemsforSlope();
                $config['slope']['order']['customerId'] = $quote->getCustomerId();

            }
        }
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
    }

    /**
     * Retrieve quote item data
     *
     * @return array
     */
    private function getQuoteItemsforSlope()
    {
        $quoteItemData = [];
        $items = [];
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            $quoteItems = $this->quoteItemRepository->getList($quoteId);
            foreach ($quoteItems as $quoteItem) {
                $product = $quoteItem->getProduct();
                $quoteItemData['id'] = $quoteItem->getItemId();
                $quoteItemData['externalId'] = $product->getId();
                $quoteItemData['sku'] = $product->getSku();
                $quoteItemData['orderId'] = $quoteId;
                $quoteItemData['name'] = $product->getName();
                $quoteItemData['description'] = $product->getDescription();
                $quoteItemData['quantity'] = $quoteItem->getTotalQty();
                $quoteItemData['unitPrice'] = $product->getPrice() * 100;
                $quoteItemData['price'] = $quoteItem->getCalculationPrice() * 100;
                $quoteItemData['url'] = $product->getProductUrl();
                $quoteItemData['createdAt'] = $product->getCreatedAt();
                $quoteItemData['updatedAt'] = $product->getUpdatedAt();
                $items[] = $quoteItemData;
            }
        }
        return $items;
    }
}
