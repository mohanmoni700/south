<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Cart\AddProductsToCart as SourceAddProductsToCart;
use Magento\Quote\Model\Cart\BuyRequest\BuyRequestBuilder;
use Magento\Quote\Model\Cart\Data\AddProductsToCartOutput;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Cart\Data\Error;
use Magento\Quote\Model\Cart\Data\CartItem;
use Avalara\Excise\Helper\Config;
use Magento\Framework\App\Request\Http;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magedelight\Subscribenow\Model\Source\SubscriptionStart;
use Magedelight\Subscribenow\Model\Subscription;
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magedelight\Subscribenow\Model\Source\BillingPeriodBy;
use Magento\Framework\Exception\LocalizedException;
use HookahShisha\Quote\Model\Cart\Data\CartItemFactory;

class AddProductsToCart extends SourceAddProductsToCart
{
    /**
     * Error message codes
     */
    private const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';
    private const ERROR_INSUFFICIENT_STOCK = 'INSUFFICIENT_STOCK';
    private const ERROR_NOT_SALABLE = 'NOT_SALABLE';
    private const ERROR_UNDEFINED = 'UNDEFINED';
    public const CUSTOMER = 'customer';

    /**
     * List of error messages and codes.
     */
    private const MESSAGE_CODES = [
        'Could not find a product with SKU' => self::ERROR_PRODUCT_NOT_FOUND,
        'The required options you selected are not available' => self::ERROR_NOT_SALABLE,
        'Product that you are trying to add is not available.' => self::ERROR_NOT_SALABLE,
        'This product is out of stock' => self::ERROR_INSUFFICIENT_STOCK,
        'There are no source items' => self::ERROR_NOT_SALABLE,
        'The fewest you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The most you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The requested qty is not available' => self::ERROR_INSUFFICIENT_STOCK,
    ];

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var array
     */
    private array $errors = [];

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @var Config
     */
    private Config $avalaraConfig;

    /**
     * @var Http
     */
    public $request;

    /**
     * @var SubscriptionService
     */
    public $service;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var ProductSubscribersInterface
     */
    private ProductSubscribersInterface $productsubscribeRepository;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var BuyRequestBuilder
     */
    private $requestBuilder;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param Json $serializer
     * @param BuyRequestBuilder $requestBuilder
     * @param Config $avalaraConfig
     * @param Http $request
     * @param SubscriptionService $service
     * @param PriceHelper $priceHelper
     * @param ProductSubscribersInterface $productsubscribeRepository
     * @param TimezoneInterface $timezone
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        Json $serializer,
        BuyRequestBuilder $requestBuilder,
        Config $avalaraConfig,
        Http $request,
        SubscriptionService $service,
        PriceHelper $priceHelper,
        ProductSubscribersInterface $productsubscribeRepository,
        TimezoneInterface $timezone,
        SubscriptionHelper $subscriptionHelper
    ) {
        parent::__construct(
            $productRepository,
            $cartRepository,
            $maskedQuoteIdToQuoteId,
            $requestBuilder
        );

        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->requestBuilder = $requestBuilder;
        $this->serializer = $serializer;
        $this->avalaraConfig = $avalaraConfig;
        $this->request = $request;
        $this->service = $service;
        $this->priceHelper = $priceHelper;
        $this->productsubscribeRepository =$productsubscribeRepository;
        $this->timezone = $timezone;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * Add cart items to the cart
     *
     * @param string $maskedCartId
     * @param CartItem[] $cartItems
     * @return AddProductsToCartOutput
     * @throws NoSuchEntityException Could not find a Cart with provided $maskedCartId
     */
    public function execute(string $maskedCartId, array $cartItems): AddProductsToCartOutput
    {
        $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        $cart = $this->cartRepository->get($cartId);

        foreach ($cartItems as $cartItemPosition => $cartItem) {
            $this->addItemToCart($cart, $cartItem, $cartItemPosition, $cartItems);
        }

        if ($cart->getData('has_error')) {
            $cartErrors = $cart->getErrors();

            /** @var MessageInterface $error */
            foreach ($cartErrors as $error) {
                $this->addError($error->getText());
            }
        }

        if (count($this->errors) !== 0) {
            /* Revert changes introduced by add to cart processes in case of an error */
            $cart->getItemsCollection()->clear();
        } else {
            // Save cart only when all items are added to cart and no errors occurred
            // restrict avalara tax request using flag for add to cart
            $this->avalaraConfig->setAddressTaxable(false);
            $this->cartRepository->save($cart);
        }

        return $this->prepareErrorOutput($cart);
    }

    /**
     * Get SuperPack array with product, total price and finalprice of each product
     *
     * @param array $superPack
     * @return array
     * @throws LocalizedException
     */
    public function getSuperPackCartItemsWithProduct($superPack)
    {
        $totalPrice = 0;
        $superPackArray = [];
        foreach ($superPack as $item) {
            $sku = $item['variant_sku'];
            // checking if same product exist
            if (in_array($sku, array_keys($superPackArray))) {
                $superPackArray[$sku]['quantity'] += 1;
                $totalPrice +=  $superPackArray[$sku]['final_price'];
            } else {
                $item['quantity'] = 1;
                try {
                    $product = $this->productRepository->get($item['sku'], false, null, true);
                } catch (NoSuchEntityException $e) {
                    throw new LocalizedException(
                        __('Could not find a product with SKU "%sku"', ['sku' => $item['sku']])
                    );
                }
                $finalPrice = $product->getFinalPrice();
                $totalPrice += $finalPrice;
                $item['final_price'] = $finalPrice;
                $item['product'] = $product;
                $superPackArray[$sku] = $item;
            }
        }
        return [$totalPrice, $superPackArray];
    }

    /**
     * Get SuperPack array
     *
     * @param CartItem $cartItem
     * @return bool | array
     */
    private function getSuperPackCartItem($cartItem)
    {
        $alfaBundle = $cartItem->getAlfaBundle();
        if ($alfaBundle) {
            $alfaBundle = $this->serializer->unserialize($alfaBundle);
            if (isset($alfaBundle['super_pack']) && $alfaBundle['super_pack'] && is_array($alfaBundle)) {
                return $this->getSuperPackCartItemsWithProduct($alfaBundle['super_pack']);
            }
        }
        return false;
    }

    /**
     * Get SuperPack array
     *
     * @param CartInterface $cart
     * @param array $item
     * @param float $finalParentPrice
     * @return Item|string
     */
    private function addSuperPackProductToCart(
        CartInterface $cart,
        array $item,
        float $finalParentPrice
    ) {
        $product = $item['product'];
        $item['super_pack_price'] = $finalParentPrice;
        $cartItem = (new CartItemFactory())->create($item);
        return $cart->addProduct($product, $this->requestBuilder->build($cartItem));
    }

    /**
     * Calculate SuperPack Final Price
     *
     * @param float $finalPrice
     * @param float $totalPrice
     * @param float $simpleProductPrice
     * @return float
     */
    private function calculateSuperPackFinalPrice(
        float $finalPrice,
        float $totalPrice,
        float $simpleProductPrice
    ):float {
        return ($finalPrice/$totalPrice) * $simpleProductPrice;
    }

    /**
     * Get the subscription service
     *
     * @return SubscriptionService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Adds a particular item to the shopping cart
     *
     * @param CartInterface|Quote $cart
     * @param Data\CartItem $cartItem
     * @param int $cartItemPosition
     * @param array $cartItems
     */
    private function addItemToCart(
        CartInterface $cart,
        CartItem $cartItem,
        int $cartItemPosition,
        array $cartItems
    ): void {
        $sku = $cartItem->getSku();

        if ($cartItem->getQuantity() <= 0) {
            $this->addError(__('The product quantity should be greater than 0')->render());
            return;
        }

        try {
            $product = $this->productRepository->get($sku, false, null, true);
        } catch (NoSuchEntityException $e) {
            $this->addError(
                __('Could not find a product with SKU "%sku"', ['sku' => $sku])->render(),
                $cartItemPosition
            );
            return;
        }
        $superPack = $this->getSuperPackCartItem($cartItem);
        
        try {
            if ($superPack) {
                list($totalPrice, $superPackArray) = $superPack;
                $qty = $cartItem->getQuantity();
                $parentAlfabundle = $cartItem->getAlfaBundle();
                $simpleProductPrice = $product->getFinalPrice();
                foreach ($superPackArray as $item) {
                    $item['quantity'] *= $qty ;
                    $item['parent_alfa_bundle'] =  $parentAlfabundle;
                    $finalPrice = $this->calculateSuperPackFinalPrice(
                        $item['final_price'],
                        $totalPrice,
                        $simpleProductPrice
                    );
                    $this->addSuperPackProductToCart($cart, $item, $finalPrice);
                }
                $cartItem->setSuperPackPrice(0);
            }
            $productid = $product->getId();

            $isSubscription = $cartItem->getIsSubscription();
            if ($isSubscription) {
                $subscriptionStartDate = $cartItem->getSubscriptionStartDate();
                $billingPeriod = $cartItem->getSubbillingPeriod();
                $endType = $cartItem->getSubendType();
                $subscriptionEndCycle = $cartItem->getSubscriptionEndCycle();
                $subscriptionEndDate = $cartItem->getSubscriptionEndDate();
                $request = [
                        'is_subscription' => $isSubscription,
                        'billing_period' => $billingPeriod,
                        'subscription_start_date' => $subscriptionStartDate,
                        'end_type' => $endType,
                        'subscription_end_cycle' => $subscriptionEndCycle,
                        'subscription_end_date' => $subscriptionEndDate
                    ];

                $this->validateSubscriptionDate($product, $request);
                $additionalOptions = [];
                $additionalOptions[] = [
                    'code' => 'is_subscription',
                    'label' => "Subscription",
                    'value' => $isSubscription
                ];
                $additionalOptions[] = [
                    'code' => 'end_type',
                    'label' => "End Type",
                    'value' => $cartItem->getSubendType()
                ];
                $additionalOptions[] = [
                    'code' => 'billing_period_title',
                    'label' => $this->subscriptionHelper->getBillingPeriodTitle(),
                    'value' => $this->getBillingPeriod($product, $request)
                ];
                $additionalOptions[] = [
                    'code' => 'billing_cycle_title',
                    'label' => $this->subscriptionHelper->getBillingCycleTitle(),
                    'value' => $this->getBillingCycle($product, $request)
                ];

                if ($product->getInitialAmount() > 0) {
                    $additionalOptions[] = [
                        'code' => 'init_amount',
                        'label' => $this->subscriptionHelper->getInitAmountTitle(),
                        'value' => $this->getOptionPriceHtml($this->getInitialAmount($product)),
                        'has_html' => true,
                    ];
                }

                if ($product->getAllowTrial() && !$this->getService()->isFutureItem($request)) {
                    $additionalOptions[] = [
                        'code' => 'trial_amount',
                        'label' => $this->subscriptionHelper->getTrialAmountTitle(),
                        'value' => $this->getOptionPriceHtml($this->getTrialAmount($product)),
                        'has_html' => true,
                    ];

                    $additionalOptions[] = [
                        'code' => 'trial_period_title',
                        'label' => $this->subscriptionHelper->getTrialPeriodTitle(),
                        'value' => $this->getTrialPeriod($product)
                    ];

                    $additionalOptions[] = [
                        'code' => 'trial_cycle_title',
                        'label' => $this->subscriptionHelper->getTrialCycleTitle(),
                        'value' => $this->getTrialCycle($product)
                    ];
                }

                $additionalOptions[] = [
                    'code' => 'md_sub_start_date',
                    'label' => $this->subscriptionHelper->getSubscriptionStartDateTitle(),
                    'value' => $this->getSubscriptionStartDate($product, $request),
                ];

                if ($this->getSubscriptionEndDate($product, $request)) {
                    $additionalOptions[] = [
                        'code' => 'md_sub_end_date',
                        'label' => $this->subscriptionHelper->getSubscriptionEndDateTitle(),
                        'value' => $this->getSubscriptionEndDate($product, $request),
                    ];
                }
            }
            $result = $cart->addProduct($product, $this->requestBuilder->build($cartItem));
            if ($isSubscription) {
                $result->addOption([
                    'product_id' => $productid,
                    'code' => 'additional_options',
                    'value' => $this->serializer->serialize($additionalOptions)
                ]);
                $cartitemqty = $cartItem->getQuantity();
                if ($result->getBuyRequest()) {
                    $buyreqoption = $result->getBuyRequest()->toArray();
                }
                $buyreqoption['qty'] = $cartitemqty;
                $buyreqoption['product'] = $productid;
                $buyreqoption['item'] = $productid;
                $buyreqoption['options'] = ['_1' => 'subscription'];
                $buyreqoption['billing_period'] = $request['billing_period'];
                $buyreqoption['subscription_start_date'] = $this->getSubscriptionStartDate($product, $request);
                $buyreqoption['subscription_end_cycle'] = $this->getBillingCycle($product, $request);
                $buyreqoption['end_type'] = $cartItem->getSubendType();
                $buyreqoption['subscription_end_date'] = $this->getSubscriptionEndDate($product, $request);
                $result->addOption([
                    'product_id' => $productid,
                    'code' => 'info_buyRequest',
                    'value' => $this->serializer->serialize($buyreqoption)
                ]);
            }
        } catch (\Throwable $e) {
            $isInAlfaBundle = $cartItem->getParentAlfaBundle();
            $alfaBundleProductType = $isInAlfaBundle
                ? $this->getAlfaBundleProductType($cartItem->getSku(), $cartItems)
                : '';
            // We use custom message for products in alfa bundle if requested qty is not available
            $useCustomMessage = ($e->getMessage() == 'The requested qty is not available') ||
                ($e->getMessage() == "This product is out of stock.");
            $customMessage = __('The requested %1 qty is not available', $alfaBundleProductType);

            $this->addError(
                __($useCustomMessage ? $customMessage : $e->getMessage())->render(),
                $cartItemPosition
            );
            $cart->setHasError(false);
            return;
        }

        if (is_string($result)) {
            $resultErrors = array_unique(explode("\n", $result));
            foreach ($resultErrors as $error) {
                $this->addError(__($error)->render(), $cartItemPosition);
            }
        }
    }

    /**
     * Validate Subscription Date
     *
     * @param object $product
     * @param array $request
     * @throws LocalizedException
     */
    public function validateSubscriptionDate($product, $request = [])
    {
        if ($request) {
            $currentDate = $this->timezone->date()->format('Y-m-d');
            $requestDate = (string) isset($request['subscription_start_date']) ?
            $request['subscription_start_date'] : '';
            $subscriptionStartDate = date('Y-m-d', strtotime($requestDate));

            if ($requestDate && $currentDate > $subscriptionStartDate) {
                throw new LocalizedException(__('Subscription start date must be greater than today.'));
            }
        }
    }

    /**
     * Add order line item error
     *
     * @param string $message
     * @param int $cartItemPosition
     * @return void
     */
    private function addError(string $message, int $cartItemPosition = 0): void
    {
        $this->errors[] = new Error(
            $message,
            $this->getErrorCode($message),
            $cartItemPosition
        );
    }

    /**
     * Get message error code.
     *
     * @param string $message
     * @return string
     */
    private function getErrorCode(string $message): string
    {
        foreach (self::MESSAGE_CODES as $codeMessage => $code) {
            if (false !== stripos($message, $codeMessage)) {
                return $code;
            }
        }

        /* If no code was matched, return the default one */
        return self::ERROR_UNDEFINED;
    }

    /**
     * Creates a new output from existing errors
     *
     * @param CartInterface $cart
     * @return AddProductsToCartOutput
     */
    private function prepareErrorOutput(CartInterface $cart): AddProductsToCartOutput
    {
        $output = new AddProductsToCartOutput($cart, $this->errors);
        $this->errors = [];
        $cart->setHasError(false);
        return $output;
    }

    /**
     * Returns alfa bundle product type (shisha || charcoal)
     *
     * @param string $sku
     * @param array $items
     * @return string
     */
    private function getAlfaBundleProductType(string $sku, array $items): string
    {
        $type = [
            'shisha_sku' => 'shisha',
            'charcoal_sku' => 'charcoal'
        ];
        $alfaBundle = [];
        foreach ($items as $item) {
            $alfaBundle = $item->getAlfaBundle();
            if ($alfaBundle) {
                $alfaBundle = $this->serializer->unserialize($alfaBundle);
                break;
            }
        }
        return $type[array_search($sku, $alfaBundle)] ?? "shisha/charcoal";
    }

    /**
     * Get Billing Period
     *
     * @param object $product
     * @param array $request
     * @return string
     */
    public function getBillingPeriod($product, $request = null)
    {
        if ($product->getBillingPeriodType() == 'customer') {
            if ($request) {
                $billingFrequency = $this->subscriptionHelper->getIntervalLabel($request['billing_period']);
            } else {
                $billingFrequency =
                $this->subscriptionHelper->getIntervalLabel($this->request->getPostValue('billing_period'));
            }
        } else {
            $optionId= $product->getBillingPeriod();
            $attribute = $product->getResource()->getAttribute('billing_period');
            if ($attribute->usesSource()) {
                $billingFrequency = $attribute->getSource()->getOptionText($optionId);
            }
        }
        return ucfirst($billingFrequency);
    }

    /**
     * Get Billing Cycle
     *
     * @param object $product
     * @param array $request
     * @return \Magento\Framework\Phrase
     */
    private function getBillingCycle($product, $request)
    {
        if ($product->getAllowSubscriptionEndDate() && isset($request['end_type'])) {
            $finalCycle = $this->endCycleCalculation($request, $product);
            return ($finalCycle) ?
                __('Repeat %1 time(s)', $finalCycle) :
                __('Repeats until failed or canceled');
        } else {
            return ($product->getBillingMaxCycles()) ?
                __('Repeat %1 time(s)', $product->getBillingMaxCycles()) :
                __("Repeats until failed or canceled");
        }
    }

    /**
     * Calculate Max billing cycle from end date
     *
     * @param array $request
     * @param object $product
     * @return false|float|int|mixed|string|null
     * @throws LocalizedException
     */
    public function endCycleCalculation($request, $product)
    {
        $endType  = $request['end_type'];
        $endCycle = $request['subscription_end_cycle'];
        if ($endType == \Magedelight\Subscribenow\Model\Subscription::END_TYPE_CYCLE) {
            return $endCycle;
        } elseif ($endType == \Magedelight\Subscribenow\Model\Subscription::END_TYPE_DATE) {
            return $this->endDateCalculation($request, $product);
        }
        return null;
    }

    /**
     * Subscription Start Date
     *
     * @param object $product
     * @param array $request
     * @return string
     */
    private function getSubscriptionStartDate($product, $request = null)
    {
        $date = $this->timezone->date();
        if ($product->getDefineStartFrom() == "defined_by_customer") {
            if ($request) {
                return $request['subscription_start_date'];
            }
            return $this->request->getPostValue('subscription_start_date');
        }
        if ($product->getDefineStartFrom() == SubscriptionStart::MOMENT) {
            return $date->format('d-m-Y');
        }

        if ($product->getDefineStartFrom() == SubscriptionStart::LAST_DAY_MONTH) {
            return $date->format('t-m-Y');
        }

        if ($product->getDefineStartFrom() == SubscriptionStart::EXACT_DAY) {
            $day = ($product->getDayOfMonth()) ? $product->getDayOfMonth() : 'd';
            $currentDate = $date->format('d-m-Y');
            $dayOfMonthDate = $date->format($day. '-m-Y');

            return ($dayOfMonthDate >= $currentDate)
                ? $dayOfMonthDate
                : $date->modify('+1 month')->format($day. '-m-Y');
        }
    }

    /**
     * Subscription Option price html
     *
     * @param string $amount
     * @return string
     */
    private function getOptionPriceHtml($amount)
    {
        if ($amount) {
            return strip_tags($amount);
        }
    }

    /**
     * Get Initial Amount with formatted price
     *
     * @param object $product
     * @return mixed
     */
    private function getInitialAmount($product)
    {
        return $this->priceHelper->currency($product->getInitialAmount(), true);
    }

    /**
     * Get Trial Amount with formatted price
     *
     * @param object $product
     * @return mixed
     */
    private function getTrialAmount($product)
    {
        if ($product->getTrialAmount()) {
            return $this->priceHelper->currency($product->getTrialAmount(), true);
        }
        return $this->priceHelper->currency(0.00);
    }

    /**
     * Trial Period
     *
     * @param object $product
     * @return string
     */
    private function getTrialPeriod($product)
    {
        $optionId= $product->getTrialPeriod();
        $attribute = $product->getResource()->getAttribute('trial_period');
        if ($attribute->usesSource()) {
            $optionText = $attribute->getSource()->getOptionText($optionId);
        }
        return $optionText;
    }

    /**
     * Trial Period Cycle
     *
     * @param object $product
     * @return string
     */
    private function getTrialCycle($product)
    {
        $defaultVal = 'Repeats until failed or canceled';
        return ($product->getTrialMaxcycle()) ? __('%1 time(s)', $product->getTrialMaxcycle()) : __($defaultVal);
    }

    /**
     * Subscription End Date
     *
     * @param object $product
     * @param array $request
     * @return string
     */
    private function getSubscriptionEndDate($product, $request = null)
    {
        if ($request['end_type'] == "md_end_date") {
            return $request['subscription_end_date'];
        }
        return false;
    }

    /**
     * Calculate End Date
     *
     * @param array $request
     * @param object $product
     * @return float
     * @throws LocalizedException
     */
    private function endDateCalculation($request, $product)
    {
        if (isset($request['billing_period']) &&
            $product->getBillingPeriodType() ==
            \Magedelight\Subscribenow\Model\Source\BillingPeriodBy::CUSTOMER) {
            $subscriptionInterval = $this->getSubscriptionInterval($request['billing_period']);
        } else {
            $billingPeriod = $this->getBillingPeriod($product, $request);
            $subscriptionInterval = $this->getSubscriptionInterval($billingPeriod);
        }

        $billingPeriod = $subscriptionInterval['interval_type'];
        $billingFrequency = $subscriptionInterval['no_of_interval'];
        $requestDate = (string)isset($request['subscription_start_date']) ? $request['subscription_start_date'] : '';
        $subscriptionStartDate = strtotime($requestDate);
        $endDate = (string)isset($request['subscription_end_date']) ? $request['subscription_end_date'] : '';
        $subscriptionEndDate = strtotime($endDate);
        $dateDiff = $subscriptionEndDate - $subscriptionStartDate;
        $dateDiff = round($dateDiff / (60 * 60 * 24));
        $year1 = date('Y', $subscriptionStartDate);
        $year2 = date('Y', $subscriptionEndDate);
        $month1 = date('m', $subscriptionStartDate);
        $month2 = date('m', $subscriptionEndDate);

        switch ($billingPeriod) {
            case 'day':
                $finalCycle = $dateDiff / $billingFrequency;
                break;
            case 'week':
                $finalCycle = ($dateDiff / $billingFrequency) / 7;
                break;
            case 'month':
                $finalCycle = (($year2 - $year1) * 12) + ($month2 - $month1);
                break;
            case 'year':
                $finalCycle = $year2 - $year1;
                break;
            default:
                $finalCycle = '';
        }
        $finalCycle = floor($finalCycle);

        if ($finalCycle < 1) {
            throw new LocalizedException(
                __('Subscription end date does not meet with selected date and frequency.')
            );
        }
        return $finalCycle;
    }
    
    /**
     * Get Subscription Interval
     *
     * @param string $key
     * @return null|array
     */
    public function getSubscriptionInterval($key = null)
    {
        $interval = $this->subscriptionHelper->getSubscriptionInterval();
        if (!empty($interval) && array_key_exists($key, $interval)) {
            return $interval[$key];
        } elseif (!empty($interval)) {
            return reset($interval);
        }
    }
}
