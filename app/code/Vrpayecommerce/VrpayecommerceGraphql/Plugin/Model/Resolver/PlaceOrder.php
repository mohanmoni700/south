<?php

namespace Vrpayecommerce\VrpayecommerceGraphql\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote as Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder as MagentoPlaceOrder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderInterfaceFactory;
use Vrpayecommerce\Vrpayecommerce\Helper\Curl;
use Vrpayecommerce\Vrpayecommerce\Helper\Payment;
use Magento\Framework\ObjectManagerInterface;
use Magento\Checkout\Model\Session;
use Vrpayecommerce\Vrpayecommerce\Model\Customer\Customer;
use Vrpayecommerce\Vrpayecommerce\Model\Payment\Information;

/**
 * This plugin validates and saves the order attribute
 */
class PlaceOrder
{
    /**
     * @var OrderInterfaceFactory
     */
    private OrderInterfaceFactory $orderFactory;

    /**
     * @var OrderInterface
     */
    private OrderInterface $order;

    /**
     * @var MethodInterface
     */
    private MethodInterface $paymentMethod;

    /**
     * @var Payment
     */
    private Payment $helperPayment;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var GetCartForUser
     */
    private GetCartForUser $getCartForUser;

    /**
     * @var Quote
     */
    private Quote $quote;

    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $_objectManager;

    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @var Customer
     */
    protected Customer $customer;

    /**
     * @var Information
     */
    private Information $information;

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param Payment $helperPayment
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param Curl $curl
     * @param ObjectManagerInterface $objectmanager
     * @param Session $checkoutSession
     * @param Customer $customer
     * @param Information $information
     */
    public function __construct(
        OrderInterfaceFactory $orderFactory,
        Payment $helperPayment,
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository,
        Curl $curl,
        ObjectManagerInterface $objectmanager,
        Session $checkoutSession,
        Customer $customer,
        Information $information
    ) {
        $this->orderFactory = $orderFactory;
        $this->helperPayment = $helperPayment;
        $this->cartRepository = $cartRepository;
        $this->getCartForUser = $getCartForUser;
        $this->curl = $curl;
        $this->_objectManager = $objectmanager;
        $this->checkoutSession = $checkoutSession;
        $this->customer = $customer;
        $this->information = $information;
    }

    /**
     * Save 'veratad_dob' value when order is placed.
     *
     * @param MagentoPlaceOrder $subject
     * @param array $return
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function afterResolve(
        MagentoPlaceOrder $subject, // NOSONAR
        $return,
        Field $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo $info, // NOSONAR
        array $value = null, // NOSONAR
        array $args = null // NOSONAR
    ) {
        $orderModel = $this->orderFactory->create();
        $this->order  = $orderModel->loadByIncrementId($return['order']['order_number'] ?? '');
        $this->paymentMethod = $this->order->getPayment()->getMethodInstance();

        if ($this->isVrPayment()) {
            $maskedCartId = $args['input']['cart_id'];
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $quote = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
            $this->quote = $quote;

            $paymentParameters = $this->getPaymentParameters();

            $paymentParameters = $this->paymentMethod->getTrackingDataForCheckoutResult($paymentParameters);
            $checkoutResult = $this->helperPayment->getCheckoutResult($paymentParameters);

            if (!$checkoutResult['isValid']) {
                throw new GraphQlInputException(__($checkoutResult['response']));
            } elseif (!isset($checkoutResult['response']['id'])) {
                throw new GraphQlInputException(__('ERROR_GENERAL_REDIRECT'));
            } else {
                $paymentWidgetUrl = $this->helperPayment->getPaymentWidgetUrl(
                    $paymentParameters['serverMode'],
                    $checkoutResult['response']['id']
                );

                $paymentWidgetContent = $this->curl->getPaymentWidgetContent(
                    $paymentWidgetUrl,
                    $paymentParameters['proxy'],
                    $paymentParameters['serverMode'],
                    $paymentParameters['bearerToken']
                );

                if (!$paymentWidgetContent['isValid'] ||
                    strpos($paymentWidgetContent['response'], 'errorDetail') !== false
                ) {
                    throw new GraphQlInputException(__('ERROR_GENERAL_REDIRECT'));
                } else {
                    $return['order']['vr_widget_url'] = $paymentWidgetUrl;
                    $return['order']['vr_brand'] = $this->paymentMethod->getBrand();
                }
            }
        }
        return $return;
    }

    /**
     * Is Vr payment
     *
     * @return boolean
     */
    public function isVrPayment()
    {
        if ($this->paymentMethod->getCode() == 'vrpayecommerce_directdebit' ||
            $this->paymentMethod->getCode() == 'vrpayecommerce_creditcard' ||
            $this->paymentMethod->getCode() == 'vrpayecommerce_ccsaved' ||
            $this->paymentMethod->getCode() == 'vrpayecommerce_ddsaved'
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get payment parameters
     *
     * @return array
     */
    protected function getPaymentParameters()
    {
        $paymentParameters = [];
        $paymentParameters = array_merge_recursive(
            $this->paymentMethod->getCredentials(),
            $this->getTransactionParameters(),
            $this->getCustomerInformationByOrder(),
            $this->getCustomerAddressByOrder(),
            $this->getCartItemsParameters(),
            $this->getEasyCreditParameters(),
            $this->getCC3DParameters(),
            $this->getCustomParameters(),
            $this->getRegistrationParameters()
        );

        return $paymentParameters;
    }

    /**
     * Get a Quote
     *
     * @return object
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->getCheckoutSession()->getQuote();
        }
        return $this->quote;
    }

    /**
     * Get a checkout session
     *
     * @return object
     */
    protected function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * Get easy credit parameters
     *
     * @return array
     */
    protected function getEasyCreditParameters()
    {
        $easyCreditParameters = [];

        if ($this->isEasyCreditPayment()) {
            $easyCreditParameters['customParameters']['RISK_ANZAHLBESTELLUNGEN'] =
                $this->customer->getCustomerOrderCount();
            $easyCreditParameters['customParameters']['RISK_BESTELLUNGERFOLGTUEBERLOGIN'] =
                $this->customer->isLoggedIn() ? 'true' : 'false';
            $easyCreditParameters['customParameters']['RISK_KUNDENSTATUS'] =
                $this->customer->getCustomerStatus();
            $easyCreditParameters['customParameters']['RISK_KUNDESEIT'] =
                $this->customer->getCustomerCreatedDate();
        }

        return $easyCreditParameters;
    }

    /**
     * Is Easycredit payment
     *
     * @return boolean
     */
    public function isEasyCreditPayment()
    {
        if ($this->paymentMethod->getCode() == 'vrpayecommerce_easycredit') {
            return true;
        }
        return false;
    }

    /**
     * Get cart items parameters
     *
     * @return array
     */
    protected function getCartItemsParameters()
    {
        $cartItemsParameters = [];

        if ($this->isKlarnaPayment() || $this->isEasyCreditPayment() || $this->isEnterpayPayment()) {
            $cartItems = [];
            $count = 0;
            $qty = 1;
            $itemId = '';
            $orderAllItems = $this->order->getAllItems();
            $parentIds = [];
            foreach ($orderAllItems as $orderItem) {
                $product = $orderItem->getProduct();
                if ($orderItem->getParentItem() !== null) {
                    $parentIds[] = $orderItem->getParentItemId();
                    $cartItems[$count]['parentId'] =  $orderItem->getParentItemId();
                }
                $finalPrice = (float)$product->getFinalPrice();
                $taxAmount = (float)$orderItem->getTaxAmount();
                $price = (float)$product->getPrice();
                $quantity = (int)$orderItem->getQtyOrdered();
                if ($this->isKlarnaPayment()) {
                    $cartItems[$count]['discount'] = ($price - $finalPrice) / $price * 100;
                    $cartItems[$count]['tax'] = $orderItem->getTaxPercent();
                }
                // need to calculate total amount to avoid errors with rounding
                if (empty($quantity)) {
                    // set quantity to 1 if it is zero or null to avoid dividing by 0
                    $quantity = 1;
                }
                $lineTotalAmount = (float) $orderItem->getRowTotal() +
                    $taxAmount - (float)$orderItem->getDiscountAmount()
                    + (float)$orderItem->getDiscountTaxCompensationAmount();
                $priceIncludeTax = $lineTotalAmount/$quantity;

                $cartItems[$count]['merchantItemId'] = (int)$product->getId();
                $cartItems[$count]['quantity'] = $quantity;
                $cartItems[$count]['name'] = $orderItem->getName();
                $cartItems[$count]['price'] = $priceIncludeTax;
                $cartItems[$count]['itemId'] = $orderItem->getItemId();
                if ($this->isEnterpayPayment()) {
                    $cartItems[$count]['tax'] = (float) $orderItem->getTaxPercent() / 100;
                    $cartItems[$count]['totalAmount'] = $lineTotalAmount;
                    $cartItems[$count]['totalTaxAmount'] = $taxAmount;
                }
                $count++;
            }

            foreach ($cartItems as $index => $cartItem) {
                if (in_array($cartItem['itemId'], $parentIds)) {
                    $keys = $this->searchForId($cartItem['itemId'], $cartItems);
                    if (!empty($keys)) {
                        foreach ($keys as $key) {
                            if ($this->isEnterpayPayment()
                                && $cartItems[$index]['tax'] !== 0
                                && $cartItems[$key]['tax'] == 0) {
                                $cartItems[$key]['tax'] = $cartItems[$index]['tax'];
                            }

                            if ($cartItems[$index]['price'] !== 0 && $cartItems[$key]['price'] == 0) {
                                $cartItems[$key]['price'] = $cartItems[$index]['price'];
                            }

                            if ($this->isEnterpayPayment()
                                && $cartItems[$index]['totalTaxAmount'] !== 0
                                && $cartItems[$key]['totalTaxAmount'] == 0
                            ) {
                                $cartItems[$key]['totalTaxAmount'] = $cartItems[$index]['totalTaxAmount'];
                            }

                            if ($this->isEnterpayPayment()
                                && $cartItems[$index]['totalAmount'] !== 0 && $cartItems[$key]['totalAmount'] == 0
                            ) {
                                $cartItems[$key]['totalAmount'] = $cartItems[$index]['totalAmount'];
                            }
                        }
                        unset($cartItems[$index]);
                    }
                }
            }
            $cartItems = array_values($cartItems);

            $shippingCosts = (float) $this->order->getShippingInclTax();
            if ($this->isEnterpayPayment() && $shippingCosts > 0) {
                // for enterpay, the sum of all cart items must match the total amount to pay, so we have to include
                // the shipping costs as position as well
                $cartItems[count($cartItems)] = [
                    'merchantItemId' => $this->order->getShippingMethod(),
                    'name' => $this->order->getShippingDescription(),
                    'quantity' => 1,
                    'price' => $shippingCosts,
                    'totalAmount' => $shippingCosts,
                    'totalTaxAmount' => (float) $this->order->getShippingTaxAmount(),
                    'tax' =>
                        $this->order->getShippingTaxAmount() /
                        ($shippingCosts - $this->order->getShippingTaxAmount())
                ];
            }
            $cartItemsParameters['cartItems'] = $cartItems;
        }

        return $cartItemsParameters;
    }

    /**
     * Checks if the current payment method is enterpay
     *
     * @return boolean
     */
    public function isEnterpayPayment()
    {
        if ($this->paymentMethod->getCode() == 'vrpayecommerce_enterpay') {
            return true;
        }
        return false;
    }

    /**
     * Is Klarna payment
     *
     * @return boolean
     */
    public function isKlarnaPayment()
    {
        if ($this->paymentMethod->getCode() == 'vrpayecommerce_klarnapaylater' ||
            $this->paymentMethod->getCode() == 'vrpayecommerce_klarnasliceit'
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get a customer information
     *
     * @return array
     */
    protected function getCustomerInformationByOrder()
    {
        $customerInformation = [];
        $customerInformation['customer']['email'] = $this->order->getBillingAddress()->getEmail();
        $customerInformation['customer']['firstName'] = $this->order->getBillingAddress()->getFirstname();
        $customerInformation['customer']['lastName'] = $this->order->getBillingAddress()->getLastname();

        $cartId = $this->getQuote()->getId();

        $quoteCart = $this->cartRepository->getActive($cartId);
        if (!$quoteCart->getCheckoutMethod()) {
            $quoteCart->setCheckoutMethod('guest');
            $this->cartRepository->save($quoteCart);
        }

        if ($this->isEasyCreditPayment()) {
            if (!$this->customer->getGender()) {
                $customerInformation['customer']['sex'] = $_COOKIE['gender'];
            } else {
                $customerInformation['customer']['sex'] = $this->customer->getGender();
            }
            if (!$this->isEasyCreditPayment()) {
                $customerInformation['customer']['birthdate'] = $this->customer->getDob();
            }
            $customerInformation['customer']['phone'] = $this->order->getBillingAddress()->getTelephone();
        }

        return $customerInformation;
    }

    /**
     * Get Credit cards 3D parameters
     *
     * @return array
     */
    protected function getCC3DParameters()
    {
        $cc3DParameters = [];

        if ($this->paymentMethod->getCode() == 'vrpayecommerce_ccsaved') {
            $cc3DParameters['3D']['amount'] = $this->order->getGrandTotal();
            $cc3DParameters['3D']['currency'] = $this->order->getOrderCurrencyCode();
        }

        return $cc3DParameters;
    }

    /**
     * Get registration parameters
     *
     * @return array
     */
    protected function getRegistrationParameters()
    {
        $registrationParameters = [];

        $isRecurring = $this->paymentMethod->isRecurring();

        $registrationParameters['registrations'] = false;
        if ($isRecurring && $this->paymentMethod->isRecurringPayment()) {
            $registrationParameters['createRegistration'] = 'true';
            $informationParamaters = $this->getInformationParamaters();
            if ($this->paymentMethod->getBrand() != 'PAYPAL') {
                $paymentInformation = $this->information->getPaymentInformation($informationParamaters);
                if (!empty($paymentInformation)) {
                    foreach ($paymentInformation as $key => $registeredPayment) {
                        $registrationParameters['registrations'][$key] = $registeredPayment['registration_id'];
                    }
                }
            }
        }

        return $registrationParameters;
    }

    /**
     * Get information parameters
     *
     * @return array
     */
    public function getInformationParamaters()
    {
        $informationParameters = [];
        $informationParameters['customerId'] = $this->customer->getId();
        $informationParameters['serverMode'] = $this->paymentMethod->getServerMode();
        $informationParameters['channelId'] = $this->paymentMethod->getChannelId();
        $informationParameters['paymentGroup'] =  $this->paymentMethod->getPaymentGroup();

        return $informationParameters;
    }

    /**
     * Get transaction parameters
     *
     * @return array
     */
    public function getTransactionParameters()
    {
        $transactionParameters = [];
        $transactionParameters['paymentType'] = $this->paymentMethod->getPaymentType();
        $transactionParameters['amount'] = $this->order->getGrandTotal();
        $transactionParameters['currency'] = $this->order->getOrderCurrencyCode();
        $transactionParameters['transactionId'] = date('dmy').time();
        $transactionParameters['customParameters']['orderId'] = $this->order->getIncrementId();
        $transactionParameters['customParameters']['paymentMethod'] =  $this->paymentMethod->getCode();

        return $transactionParameters;
    }

    /**
     * Get a customer address
     *
     * @return array
     */
    protected function getCustomerAddressByOrder()
    {
        $customerAddresss = [];
        $customerAddresss['billing']['street'] = implode(' ', $this->order->getBillingAddress()->getStreet());
        $customerAddresss['billing']['city'] = $this->order->getBillingAddress()->getCity();
        $customerAddresss['billing']['zip'] = $this->order->getBillingAddress()->getPostcode();
        $customerAddresss['billing']['state'] = $this->order->getBillingAddress()->getRegion();
        $customerAddresss['billing']['countryCode'] = $this->order->getBillingAddress()->getCountryId();

        return $customerAddresss;
    }

    /**
     * Get custom parameters
     *
     * @return array
     */
    public function getCustomParameters()
    {
        $customParameters = [];
        $customParameters['customParameters']['SHOP_VERSION'] = $this->paymentMethod->getShopVersion();
        $customParameters['customParameters']['PLUGIN_VERSION'] = $this->paymentMethod->getPluginVersion();

        if ($this->order->getData('customer_taxvat')) {
            $customParameters['customParameters']['buyerCompanyVat'] = $this->order->getData('customer_taxvat');
        }

        return $customParameters;
    }
}
