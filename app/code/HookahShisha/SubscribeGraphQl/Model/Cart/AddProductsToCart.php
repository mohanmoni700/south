<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Cart;

use Magedelight\Subscribenow\Model\Subscription;
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
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magento\Framework\Exception\LocalizedException;
use HookahShisha\Quote\Model\Cart\Data\CartItemFactory;
use Magedelight\Subscribenow\Model\Source\BillingPeriodBy;

class AddProductsToCart extends SourceAddProductsToCart
{
    /**
     * Error message code product
     */
    private const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';

    /**
     * Error message code stock
     */
    private const ERROR_INSUFFICIENT_STOCK = 'INSUFFICIENT_STOCK';

    /**
     * Error message code salable
     */
    private const ERROR_NOT_SALABLE = 'NOT_SALABLE';

    /**
     * Error message code undefined
     */
    private const ERROR_UNDEFINED = 'UNDEFINED';

    /**
     * Customer
     */
    public const CUSTOMER = 'customer';

    /**
     * Subscription notification message
     */
    public const REPEATS_UNTIL_FAILED_OR_CANCELED = 'Repeats until failed or canceled';

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
        ProductRepositoryInterface      $productRepository,
        CartRepositoryInterface         $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        Json                            $serializer,
        BuyRequestBuilder               $requestBuilder,
        Config                          $avalaraConfig,
        Http                            $request,
        SubscriptionService             $service,
        PriceHelper                     $priceHelper,
        ProductSubscribersInterface     $productsubscribeRepository,
        TimezoneInterface               $timezone,
        SubscriptionHelper              $subscriptionHelper
    )
    {
        parent::__construct(
            $productRepository,
            $cartRepository,
            $maskedQuoteIdToQuoteId,
            $requestBuilder,
            $avalaraConfig
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
        $this->productsubscribeRepository = $productsubscribeRepository;
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
        CartItem      $cartItem,
        int           $cartItemPosition,
        array         $cartItems
    ): void
    {
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

        try {
            $productId = $product->getId();
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
                $this->validateSubscriptionDate($request);
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
                if ($this->getSubscriptionEndDate($request)) {
                    $additionalOptions[] = [
                        'code' => 'md_sub_end_date',
                        'label' => $this->subscriptionHelper->getSubscriptionEndDateTitle(),
                        'value' => $this->getSubscriptionEndDate($request),
                    ];
                }
            }
            $result = $cart->addProduct($product, $this->requestBuilder->build($cartItem));
            if ($isSubscription) {
                $result->addOption([
                    'product_id' => $productId,
                    'code' => 'additional_options',
                    'value' => $this->serializer->serialize($additionalOptions)
                ]);
                $cartitemqty = $cartItem->getQuantity();
                if ($result->getBuyRequest()) {
                    $buyreqoption = $result->getBuyRequest()->toArray();
                }
                $buyreqoption['qty'] = $cartitemqty;
                $buyreqoption['product'] = $productId;
                $buyreqoption['item'] = $productId;
                $buyreqoption['options'] = ['_1' => 'subscription'];
                $buyreqoption['billing_period'] = $request['billing_period'];
                $buyreqoption['subscription_start_date'] = $this->getSubscriptionStartDate($product, $request);
                $buyreqoption['subscription_end_cycle'] = $this->getBillingCycle($product, $request);
                $buyreqoption['end_type'] = $cartItem->getSubendType();
                $buyreqoption['subscription_end_date'] = $this->getSubscriptionEndDate($request);
                $result->addOption([
                    'product_id' => $productId,
                    'code' => 'info_buyRequest',
                    'value' => $this->serializer->serialize($buyreqoption)
                ]);
            }
        } catch (\Throwable $e) {
            $this->addError(
                __($e->getMessage())->render(),
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
    public function validateSubscriptionDate($request = [])
    {
        if ($request) {
            $currentDate = $this->timezone->date()->format('Y-m-d');
            $requestDate = (string)isset($request['subscription_start_date']) ?
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
     * Get Billing Period
     *
     * @param object $product
     * @param array $request
     * @return string
     */
    public function getBillingPeriod($product, $request = null)
    {
        $billingFrequency = '';
        if ($product->getBillingPeriodType() == 'customer') {
            if ($request) {
                $billingFrequency = $this->subscriptionHelper->getIntervalLabel($request['billing_period']);
            } else {
                $billingFrequency =
                    $this->subscriptionHelper->getIntervalLabel($this->request->getPostValue('billing_period'));
            }
        } else {
            $optionId = $product->getBillingPeriod();
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
                __($finalCycle) :
                __(self::REPEATS_UNTIL_FAILED_OR_CANCELED);
        } else {
            return ($product->getBillingMaxCycles()) ?
                __('Repeat %1 time(s)', $product->getBillingMaxCycles()) :
                __(self::REPEATS_UNTIL_FAILED_OR_CANCELED);
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
        $endType = $request['end_type'];
        $endCycle = $request['subscription_end_cycle'];
        if ($endType == Subscription::END_TYPE_CYCLE) {
            return $endCycle;
        } elseif ($endType == Subscription::END_TYPE_DATE) {
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
        if ($product->getDefineStartFrom() == "defined_by_customer") {
            if ($request) {
                return $request['subscription_start_date'];
            }
            return $this->request->getPostValue('subscription_start_date');
        }
        $this->service->getSubscriptionStartDate();
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
        return false;
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
        $optionText = '';
        $optionId = $product->getTrialPeriod();
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
        $defaultVal = self::REPEATS_UNTIL_FAILED_OR_CANCELED;
        return ($product->getTrialMaxcycle()) ? __('%1 time(s)', $product->getTrialMaxcycle()) : __($defaultVal);
    }

    /**
     * Subscription End Date
     *
     * @param object $product
     * @param array $request
     * @return string
     */
    private function getSubscriptionEndDate($request = null)
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
            $product->getBillingPeriodType() == BillingPeriodBy::CUSTOMER) {
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

        if (is_float($finalCycle)) {
            $finalCycleArray = explode(".", (string)$finalCycle);
            if (isset($finalCycleArray[1]) && $finalCycleArray[1] > 0) {
                $finalCycle = $finalCycle + 1;
            }
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
