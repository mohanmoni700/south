<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller;

class Payment extends \Magento\Framework\App\Action\Action
{
    protected $quote = false;
    public $order = false;
    protected $checkoutSession;
    protected $localeResolver;
    protected $resultPageFactory;
    public $helperPayment;
    protected $information;
    public $paymentMethod;
    protected $quoteManagement;
    protected $customer;
    protected $klarnaCartItemFlags = '32';
    public $vrpayecommerce = 'vrpayecommerce';
    public $curl;

    /**
     *
     * @param \Magento\Framework\App\Action\Context                    $context
     * @param \Magento\Checkout\Model\Session                          $checkoutSession
     * @param \Magento\Framework\Locale\ResolverInterface              $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory               $resultPageFactory
     * @param \Vrpayecommerce\Vrpayecommerce\Helper\Payment               $helperPayment
     * @param \Vrpayecommerce\Vrpayecommerce\Model\Payment\Information $information
     * @param \Vrpayecommerce\Vrpayecommerce\Model\Customer\Customer   $customer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Vrpayecommerce\Vrpayecommerce\Helper\Payment $helperPayment,
        \Vrpayecommerce\Vrpayecommerce\Helper\Curl $curl,
        \Vrpayecommerce\Vrpayecommerce\Model\Payment\Information $information,
        \Vrpayecommerce\Vrpayecommerce\Model\Customer\Customer $customer
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        $this->resultPageFactory = $resultPageFactory;
        $this->helperPayment = $helperPayment;
        $this->curl = $curl;
        $this->information = $information;
        $this->customer = $customer;
    }

    /**
     *
     * @return void
     */
    public function execute()
    {
        // handle cancellation of a payment widget
        $is_cancel = $this->getRequest()->getParam('is_cancel');
        if (isset($is_cancel) && $is_cancel)
        {
            $this->cancelWidget();
            return;
        }

        $this->setPaymentMethod();

        if ($this->checkoutSession->getSessionId() ==  $this->checkoutSession->getLastSessionId()) {
            $this->redirectError('ERROR_MULTIPLE_PAYMENT');
        }

        $isServerToServer = $this->paymentMethod->isServerToServer();
        if ($this->paymentMethod->isServerToServer()) {
            $isAvailableMethodByAddress = $this->isAvailableMethodByAddress();
            if ($isAvailableMethodByAddress) {
                return $this->processServerToServer();
            } else {
                $this->redirectError('ERROR_MESSAGE_BILLING_SHIPPING_NOTSAME');
            }
        } else {
            return $this->processCheckout();
        }
    }

    /**
     * set paymentMethod
     * @return void
     */
    protected function setPaymentMethod()
    {
        $this->order = $this->getOrder();
        $this->paymentMethod = $this->order->getPayment()->getMethodInstance();
    }

    /**
     * check if a customer has a billing address same as a shipping address then the klarna payment method is available
     * @return boolean
     */
    protected function isAvailableMethodByAddress()
    {
        if ($this->isKlarnaPayment()) {
            $isShippingAddressSameAsBilling = $this->paymentMethod->isShippingAddressSameAsBilling();

            if (!$isShippingAddressSameAsBilling) {
                return false;
            }
        }
        return true;
    }

    /**
     * process checkout for server to server method
     * @return object
     */
    protected function processServerToServer()
    {
        $checkoutParameters = array_merge_recursive(
            $this->getPaymentParameters(),
            $this->getServerToServerParameters()
        );
        $paymentMethod = $this->paymentMethod;
        $initialPaymentResponse = $this->helperPayment->initializeServerToServerPayment($checkoutParameters);
        if ($initialPaymentResponse['isValid']) {
            if (isset($initialPaymentResponse['response']['redirect']['url'])) {
                // check if method is given else set to POST
                if(!isset($initialPaymentResponse['response']['redirect']['method']) || empty($initialPaymentResponse['response']['redirect']['method']))
                {
                    $initialPaymentResponse['response']['redirect']['method'] = 'POST';
                }
                $resultPageFactory = $this->resultPageFactory->create();
                // Add page title
                $resultPageFactory->getConfig()->getTitle()->set($paymentMethod->getTitle());
                $blockVrpayecommerce = $resultPageFactory->getLayout()->getBlock('vrpayecommercePaymentForm');
                $blockVrpayecommerce->setTemplate('Vrpayecommerce_Vrpayecommerce::payment/responseredirect.phtml');
                $blockVrpayecommerce->setInitialPaymentResponse($initialPaymentResponse['response']);

                return $resultPageFactory;
            }
        }
        $this->redirectError('ERROR_GENERAL_REDIRECT');
    }

    /**
     * process checkout for copy and pay method
     * @return object $resultPageFactory;
     */
    protected function processCheckout()
    {
        if ($this->order->getState() == "processing") {
            $this->_redirect('checkout/onepage/success');
        }
        $paymentParameters = $this->getPaymentParameters();

        $paymentParameters = $this->paymentMethod->getTrackingDataForCheckoutResult($paymentParameters);

        $checkoutResult = $this->helperPayment->getCheckoutResult($paymentParameters);

        if (!$checkoutResult['isValid']) {
            $this->redirectError($checkoutResult['response']);
        } elseif (!isset($checkoutResult['response']['id'])) {
            $this->redirectError('ERROR_GENERAL_REDIRECT');
        } else {
            $paymentWidgetUrl  = $this->helperPayment->getPaymentWidgetUrl(
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
                $this->redirectError('ERROR_GENERAL_REDIRECT');
            }

            $resultPageFactory = $this->resultPageFactory->create();
            // Add page title
            $resultPageFactory->getConfig()->getTitle()->set($this->paymentMethod->getTitle());
            $this->addBreadCrumbs($resultPageFactory);

            $cancelUrl = $this->_url->getUrl(
                'vrpayecommerce/payment',
                [
                    'is_cancel' => true,
                    '_secure' => true
                ]
            );
            $responseUrl = $this->_url->getUrl(
                'vrpayecommerce/payment/response',
                [
                    'payment_method' => $this->paymentMethod->getCode(),
                    '_secure' => true
                ]
            );
            $this->setPageAsset($resultPageFactory);

            $blockVrpayecommerce = $resultPageFactory->getLayout()->getBlock('vrpayecommercePaymentForm');
            if ($this->paymentMethod->getCode() == 'vrpayecommerce_paypalsaved') {
                $paymentParameters['registrations'] = $this->getRegisteredPaypal();
                $this->setPaypalSavedBlock($blockVrpayecommerce);
            }
            $blockVrpayecommerce->setPaymentCode($this->paymentMethod->getCode());
            $blockVrpayecommerce->setBrand($this->paymentMethod->getBrand());
            $blockVrpayecommerce->setIsRecurringPayment($this->paymentMethod->isRecurringPayment());
            $blockVrpayecommerce->setRegistrations($paymentParameters['registrations']);
            $blockVrpayecommerce->setWidgetStyle($this->paymentMethod->getWidgetStyle());
            $blockVrpayecommerce->setLang($this->paymentMethod->getLangCode());
            $blockVrpayecommerce->setTestMode($this->paymentMethod->getTestMode());
            $blockVrpayecommerce->setCancelUrl($cancelUrl);
            $blockVrpayecommerce->setResponseUrl($responseUrl);
            $blockVrpayecommerce->setPaymentWidgetUrl($paymentWidgetUrl);
            $blockVrpayecommerce->setMerchantLocation($this->paymentMethod->getGeneralConfig('merchant_location'));

            return $resultPageFactory;
        }
    }

    /**
     * this method is executed if a payment widgets gets canceled
     * therefore resets the lastSessionId and redirects to 'checkout/cart'
     */
    public function cancelWidget()
    {
        $this->checkoutSession->setLastSessionId(null); // reset last session id to allow another payment attempt
        $url = 'checkout/cart';
        $this->_redirect($url, ['_secure' => true]);
    }

    /**
     * set a paypal saved block
     * @param object $blockVrpayecommerce
     */
    protected function setPaypalSavedBlock($blockVrpayecommerce)
    {
        $blockVrpayecommerce->setTemplate('Vrpayecommerce_Vrpayecommerce::payment/paypalsaved.phtml');
        $controller = 'vrpayecommerce/payment/response';
        $repeatedPaypalresponseUrl = $this->_url->getUrl(
            $controller,
            [
                'payment_method' => $this->paymentMethod->getCode(),
                'repeated_paypal' => true,
                '_secure' => true
            ]
        );
        $formKey = $this->_objectManager->create('Magento\Framework\Data\Form\FormKey');
        $blockVrpayecommerce->setFormKey($formKey->getFormKey());
        $blockVrpayecommerce->setOrderId($this->getOrderIncrementId());
        $blockVrpayecommerce->setRepeatedPaypalResponseUrl($repeatedPaypalresponseUrl);
    }

    /**
     * add bread crumbs
     * @param object $resultPageFactory
     */
    protected function addBreadCrumbs($resultPageFactory)
    {
        // Add breadcrumb
        /** @var \Magento\Theme\Block\Html\Breadcrumbs */
        $breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        $breadcrumbs->addCrumb(
            $this->paymentMethod->getCode(),
            [
                'label' => $this->paymentMethod->getTitle(),
                'title' => $this->paymentMethod->getTitle()
            ]
        );
    }

    /**
     * set a page asset
     * @param object $resultPageFactory
     */
    protected function setPageAsset($resultPageFactory)
    {
        $widgetStyle = $this->paymentMethod->getWidgetStyle();
        if ($widgetStyle == 'custom') {
            switch ($this->paymentMethod->getCode()) {
                case 'vrpayecommerce_ccsaved':
                case 'vrpayecommerce_creditcard':
                case 'vrpayecommerce_ddsaved':
                case 'vrpayecommerce_directdebit':
                    $resultPageFactory->getConfig()->addPageAsset(
                        'Vrpayecommerce_Vrpayecommerce/css/payment_form_custom.css'
                    );
                    break;
                default:
                    $resultPageFactory->getConfig()->addPageAsset('Vrpayecommerce_Vrpayecommerce/css/payment_form.css');
                    break;
            }
        } else {
            $resultPageFactory->getConfig()->addPageAsset('Vrpayecommerce_Vrpayecommerce/css/payment_form.css');
        }
    }

    /**
     * set a page asset for my payment information page
     * @param [type] $resultPageFactory
     */
    protected function setPageAssetRecurring($resultPageFactory)
    {
        $widgetStyle = $this->paymentMethod->getWidgetStyle();
        if ($widgetStyle == 'custom') {
            $resultPageFactory->getConfig()->addPageAsset(
                'Vrpayecommerce_Vrpayecommerce/css/recurring_form_custom.css'
            );
        } else {
            $resultPageFactory->getConfig()->addPageAsset('Vrpayecommerce_Vrpayecommerce/css/recurring_form.css');
        }
    }

    /**
     * check if the VR pay eCommerce payment methods are available
     * @param  string  $paymentMethod
     * @return boolean
     */
    public function isVrpayecommerceMethod($paymentMethod)
    {
        $vrpayecommerceMethod = strpos($paymentMethod, $this->vrpayecommerce);
        if ($vrpayecommerceMethod !== false) {
            return true;
        }
        return false;
    }

    /**
     * create a payment method object
     * @param  string $paymentMethod
     * @return object
     */
    public function createPaymentMethodObjectByPaymentMethod($paymentMethod)
    {
        if ($this->isVrpayecommerceMethod($paymentMethod))
        {
            $paymentMethodNameSpace = 'Vrpayecommerce\Vrpayecommerce\Model\Method\\'.
                $this->getPaymentMethodClassName($paymentMethod);
            return $this->_objectManager->create($paymentMethodNameSpace);
        }
    }

    /**
     * get a payment method class name
     * @param  string $paymentMethod
     * @return string
     */
    public function getPaymentMethodClassName($paymentMethod)
    {
        $methods = [
            'ccsaved' => 'CCSaved',
            'creditcard' => 'CreditCard',
            'ddsaved' => 'DDSaved',
            'directdebit' => 'DirectDebit',
            'easycredit' => 'EasyCredit',
            'giropay' => 'Giropay',
            'klarnasliceit' => 'Klarnasliceit',
            'klarnapaylater' => 'Klarnapaylater',
            'paydirekt' => 'Paydirekt',
            'paypal' => 'Paypal',
            'paypalsaved' => 'PaypalSaved',
            'klarnaobt' => 'Klarnaobt',
            'enterpay' => 'Enterpay'
        ];

        $code = str_replace('vrpayecommerce_', '', $paymentMethod);

        if (isset($methods[$code])) {
            return $methods[$code];
        }

        return 'AbstractMethod';
    }

    /**
     * get payment parameters
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
            $this->getKlarnaParameters(),
            $this->getEasyCreditParameters(),
            $this->getPaydirektParameters(),
            $this->getCC3DParameters(),
            $this->getCustomParameters(),
            $this->getRegistrationParameters()
        );

        return $paymentParameters;
    }

    /**
     * get the server to server parameters
     * @return array
     */
    protected function getServerToServerParameters()
    {
        $serverToServerParameters = [];

        $paymentMethod = $this->paymentMethod;

        $serverToServerParameters['paymentBrand'] = $paymentMethod->getBrand();
        $serverToServerParameters['shopperResultUrl'] =
            $this->_url->getUrl(
                'vrpayecommerce/payment/response',
                [
                    'payment_method' => $paymentMethod->getCode(),
                    'server_to_server' => $paymentMethod::ASYNCHRONOUS,
                    '_secure' => true
                ]
            );

        return $serverToServerParameters;
    }

    /**
     * get registration parameters
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
     * get registered paypal accounts
     * @return array
     */
    protected function getRegisteredPaypal()
    {
        $isRecurring = $this->paymentMethod->isRecurring();

        $registeredPaypal = false;
        if ($isRecurring
            && $this->paymentMethod->isRecurringPayment()
            && $this->paymentMethod->getCode() == 'vrpayecommerce_paypalsaved') {
            $informationParamaters = $this->getInformationParamaters();
            $paymentInformation = $this->information->getPaymentInformation($informationParamaters);
            if (!empty($paymentInformation)) {
                foreach ($paymentInformation as $key => $registeredPayment) {
                    $registeredPaypal[$key]['registrationId'] = $registeredPayment['registration_id'];
                    $registeredPaypal[$key]['email'] = $registeredPayment['email'];
                }
            }
        }

        return $registeredPaypal;
    }

    /**
     * get order increment id
     * @return string
     */
    protected function getOrderIncrementId()
    {
        return $this->order->getIncrementId();
    }

    /**
     * get order currency
     * @return string
     */
    protected function getOrderCurrency()
    {
        return $this->order->getOrderCurrencyCode();
    }

    /**
     * get transaction parameters
     * @return array
     */
    public function getTransactionParameters()
    {
        $transactionParameters = [];
        if ($this->isSendPaymentTypeParameter()) {
            $transactionParameters['paymentType'] = $this->paymentMethod->getPaymentType();
        }
        $transactionParameters['amount'] = $this->order->getGrandTotal();
        $transactionParameters['currency'] = $this->getOrderCurrency();
        $transactionParameters['transactionId'] = date('dmy').time();
        $this->checkoutSession->setTransactionId($transactionParameters['transactionId']);
        $transactionParameters['customParameters']['orderId'] = $this->getOrderIncrementId();
        $this->checkoutSession->setOrderId($transactionParameters['customParameters']['orderId']);
        $transactionParameters['customParameters']['paymentMethod'] =  $this->paymentMethod->getCode();
        $this->checkoutSession->setLastSessionId($this->checkoutSession->getSessionId());

        return $transactionParameters;
    }

    /**
     * get a customer information
     * @return array
     */
    protected function getCustomerInformationByOrder()
    {
        $customerInformation = [];
        $customerInformation['customer']['email'] = $this->order->getBillingAddress()->getEmail();
        $customerInformation['customer']['firstName'] = $this->order->getBillingAddress()->getFirstname();
        $customerInformation['customer']['lastName'] = $this->order->getBillingAddress()->getLastname();

        $quoteCartRepository = $this->_objectManager->create('Magento\Quote\Api\CartRepositoryInterface');

        $cartId = $this->getQuote()->getId();

        $quoteCart = $quoteCartRepository->getActive($cartId);
        if (!$quoteCart->getCheckoutMethod()) {
            $quoteCart->setCheckoutMethod('guest');
            $quoteCartRepository->save($quoteCart);
        }

        if ($this->isKlarnaPayment() || $this->isEasyCreditPayment()) {
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
     * get a customer address
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

        if ($this->isKlarnaPayment() || $this->isEasyCreditPayment() || $this->isEnterpayPayment()) {
            $customerAddresss['shipping']['street'] = implode(' ', $this->order->getShippingAddress()->getStreet());
            $customerAddresss['shipping']['city'] = $this->order->getShippingAddress()->getCity();
            $customerAddresss['shipping']['zip'] = $this->order->getShippingAddress()->getPostcode();
            $customerAddresss['shipping']['state'] = $this->order->getShippingAddress()->getRegion();
            $customerAddresss['shipping']['countryCode'] = $this->order->getShippingAddress()->getCountryId();
        }

        return $customerAddresss;
    }

    /**
     * is Klarna payment
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
     * get klarna parameters
     * @return array
     */
    protected function getKlarnaParameters()
    {
        $klarnaParameters = [];

        if ($this->isKlarnaPayment()) {
            $klarnaParameters['customParameters']['klarnaCartItem1Flags'] = $this->klarnaCartItemFlags;

            if ($this->paymentMethod->getCode() == 'vrpayecommerce_klarnasliceit') {
                $klarnaParameters['customParameters']['klarnaPclassFlag'] =
                    $this->paymentMethod->getConfigData('pclass_id');
            }
        }

        return $klarnaParameters;
    }

    /**
     * is Easycredit payment
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
     * get easy credit parameters
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
     * checks if the current payment method is enterpay
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
     * get cart items parameters
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
                if($orderItem->getParentItem() !== null){
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
                if (empty($quantity))
                {
                    // set quantity to 1 if it is zero or null to avoid dividing by 0
                    $quantity = 1;
                }
                $lineTotalAmount = (float) $orderItem->getRowTotal() + $taxAmount - (float)$orderItem->getDiscountAmount() + (float)$orderItem->getDiscountTaxCompensationAmount();
                $priceIncludeTax = $lineTotalAmount/$quantity;

                $cartItems[$count]['merchantItemId'] = (int)$product->getId();
                $cartItems[$count]['quantity'] = $quantity;
                $cartItems[$count]['name'] = $orderItem->getName();
                $cartItems[$count]['price'] = $priceIncludeTax;
                $cartItems[$count]['itemId'] = $orderItem->getItemId();
                if($this->isEnterpayPayment())
                {
                    $cartItems[$count]['tax'] = (float) $orderItem->getTaxPercent() / 100;
                    $cartItems[$count]['totalAmount'] = $lineTotalAmount;
                    $cartItems[$count]['totalTaxAmount'] = $taxAmount;
                }
                $count++;
            }

            foreach($cartItems as $index => $cartItem){
                if(in_array($cartItem['itemId'],$parentIds)){
                    $keys = $this->searchForId($cartItem['itemId'],$cartItems);
                    if(!empty($keys)) {
                        foreach ($keys as $key) {
                            if ($this->isEnterpayPayment() && $cartItems[$index]['tax'] !== 0 && $cartItems[$key]['tax'] == 0) {
                                $cartItems[$key]['tax'] = $cartItems[$index]['tax'];
                            }

                            if ($cartItems[$index]['price'] !== 0 && $cartItems[$key]['price'] == 0) {
                                $cartItems[$key]['price'] = $cartItems[$index]['price'];
                            }

                            if ($this->isEnterpayPayment() && $cartItems[$index]['totalTaxAmount'] !== 0 && $cartItems[$key]['totalTaxAmount'] == 0) {
                                $cartItems[$key]['totalTaxAmount'] = $cartItems[$index]['totalTaxAmount'];
                            }

                            if ($this->isEnterpayPayment() && $cartItems[$index]['totalAmount'] !== 0 && $cartItems[$key]['totalAmount'] == 0) {
                                $cartItems[$key]['totalAmount'] = $cartItems[$index]['totalAmount'];
                            }
                        }
                        unset($cartItems[$index]);
                    }
                }
            }
            $cartItems = array_values($cartItems);

            $shippingCosts = (float) $this->order->getShippingInclTax();
            if($this->isEnterpayPayment() && $shippingCosts > 0)
            {
                // for enterpay, the sum of all cart items must match the total amount to pay, so we have to include
                // the shipping costs as position as well
                $cartItems[count($cartItems)] = [
                    'merchantItemId' => $this->order->getShippingMethod(),
                    'name' => $this->order->getShippingDescription(),
                    'quantity' => 1,
                    'price' => $shippingCosts,
                    'totalAmount' => $shippingCosts,
                    'totalTaxAmount' => (float) $this->order->getShippingTaxAmount(),
                    'tax' => $this->order->getShippingTaxAmount() / ($shippingCosts - $this->order->getShippingTaxAmount())
                ];
            }


            $cartItemsParameters['cartItems'] = $cartItems;
        }

        return $cartItemsParameters;
    }

    protected function searchForId($id, $array) {
        $keys = [];
        foreach ($array as $key => $val) {
            if (isset($val['parentId']) && $val['parentId'] === $id) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    /**
     * get paydirekt parameters
     * @return array
     */
    protected function getPaydirektParameters()
    {
        $paydirektParameters = [];

        if ($this->paymentMethod->getCode() == 'vrpayecommerce_paydirekt') {
            $paydirektParameters['customParameters']['paydirektMinimumAge'] =
                $this->paymentMethod->getConfigData('minimum_age');
            $paydirektParameters['customParameters']['paydirektPaymentIsPartial'] =
                $this->paymentMethod->paydirektPaymentIsPartial();
        }

        return $paydirektParameters;
    }

    /**
     * get Credit cards 3D parameters
     * @return array
     */
    protected function getCC3DParameters()
    {
        $cc3DParameters = [];

        if ($this->paymentMethod->getCode() == 'vrpayecommerce_ccsaved') {
            $cc3DParameters['3D']['amount'] = $this->order->getGrandTotal();
            $cc3DParameters['3D']['currency'] = $this->getOrderCurrency();
            ;
        }

        return $cc3DParameters;
    }

    /**
     * get custom parameters
     * @return array
     */
    public function getCustomParameters()
    {
        $customParameters = [];

        $customParameters['customParameters']['SHOP_VERSION'] = $this->paymentMethod->getShopVersion();
        $customParameters['customParameters']['PLUGIN_VERSION'] = $this->paymentMethod->getPluginVersion();

//        if ($this->order->getData('customer_taxvat'))
//        {
//            $customParameters['customParameters']['buyerCompanyVat'] = $this->order->getData('customer_taxvat');
//        }

        return $customParameters;
    }

    /**
     * get paypal saved parameters
     * @param  boolean $registrationId
     * @param  boolean $transactionId
     * @return array
     */
    protected function getPaypalSavedParameters($registrationId = false, $transactionId = false)
    {
        $paypalSavedParameters = [];
        $paypalSavedParameters = array_merge(
            $this->paymentMethod->getCredentials(),
            $this->getTransactionParameters(),
            $this->getCustomParameters()
        );
        $paypalSavedParameters['paymentType'] = $this->paymentMethod->getPaymentType();
        if ($registrationId) {
            $paypalSavedParameters['transactionId'] = $transactionId;
            $paypalSavedParameters['recurringType'] = 'INITIAL';
        } else {
            $paypalSavedParameters['recurringType'] = 'REPEATED';
        }

        return $paypalSavedParameters;
    }

    /**
     * check if the PayPal recurring is used then the payment type is not sent
     * @return boolean
     */
    protected function isSendPaymentTypeParameter()
    {
        if ($this->paymentMethod->getCode() == 'vrpayecommerce_paypalsaved'
            && !$this->paymentMethod->isAdminLoggedIn()
        ) {
            return false;
        }
        return true;
    }

    /**
     * get recurring parameters
     * @param  boolean|string $transactionId
     * @return array
     */
    protected function getRecurringParameters($transactionId = false)
    {
        $recurringParameters = [];
        $recurringParameters = array_merge(
            $this->paymentMethod->getCredentials(),
            $this->customer->getCustomerInformation(),
            $this->customer->getDefaultBillingAddress(),
            $this->getCustomParameters()
        );
        $recurringParameters['amount'] = $this->paymentMethod->getRegisterAmount();
        $recurringParameters['currency'] = $this->getRecurringCurrency();

        if ($this->paymentMethod->getCode() != 'vrpayecommerce_paypalsaved') {
            $recurringParameters['paymentType'] = $this->paymentMethod->getPaymentType();
        }
        $recurringParameters['transactionId'] = $this->getRecurringParametersTransactionId($transactionId);
        $recurringParameters['recurringType'] = 'INITIAL';
        $recurringParameters['createRegistration'] = 'true';

        return $recurringParameters;
    }

    /**
     * get a transaction id of payment recurring
     * @param  boolean $transactionId
     * @return string
     */
    protected function getRecurringParametersTransactionId($transactionId = false)
    {
        if ($transactionId) {
            return $transactionId;
        } else {
            return $this->customer->getId();
        }
    }

    /**
     * get a recurring currency
     * @return [type]
     */
    protected function getRecurringCurrency()
    {
        $storeManager = $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore();
        return $store->getCurrentCurrencyCode();
    }

    /**
     * redirect to the payment error page
     * @param  string  $errorIdentifier
     * @param  boolean|string $transactionId
     * @param  string  $url
     * @return void
     */
    public function redirectError($errorIdentifier, $transactionId = false, $url = 'checkout/cart')
    {
        $this->checkoutSession->setLastSessionId(null); // reset last session id to allow another payment attempt
        if ($this->order instanceof \Magento\Sales\Model\Order) {
            $this->order->cancel()->save();
        }
        $errorMessage = __($errorIdentifier);
        if ($transactionId) {
            $errorMessage .= ' ('.__('BACKEND_TT_TRANSACTION_ID').' : '.$transactionId.')';
        }
        $this->messageManager->addError($errorMessage);
        $this->_redirect($url, ['_secure' => true]);
    }

    /**
     * redirect to the payment error page at my payment information
     * @param  string $generalError
     * @param  string $detailError
     * @param  string $informationId
     * @param  string $url
     * @return string
     */
    protected function redirectErrorRecurring(
        $generalError,
        $detailError = null,
        $informationId = null,
        $url = 'vrpayecommerce/payment/information'
    ) {
        if ($generalError) {
            $errorMessage = __($generalError);
        } elseif ($informationId) {
            $errorMessage = __('ERROR_MC_UPDATE');
        } else {
            $errorMessage = __('ERROR_MC_ADD');
        }
        if ($detailError) {
            $errorMessage .= ' : '.__($detailError);
        }
        $this->checkoutSession->setLastSessionId(null);
        $this->messageManager->addError($errorMessage);
        $this->_redirect($url, ['_secure' => true]);
    }

    /**
     * redirect to the register or change success page
     * @param  string $successIdentifier
     * @param  string $url
     * @return string
     */
    protected function redirectSuccessRecurring($successIdentifier, $url = 'vrpayecommerce/payment/information')
    {
        $this->messageManager->addSuccess(__($successIdentifier));
        $this->_redirect($url, ['_secure' => true]);
    }

    /**
     * get a checkout session
     * @return object
     */
    protected function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * get a Quote
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
     * get an order
     * @return object
     */
    protected function getOrder()
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->load($this->checkoutSession->getLastOrderId());

        return $order;
    }

    /**
     * get an order based on increment id
     * @param  int $incrementId
     * @return object
     */
    protected function getOrderByIncerementId($incrementId)
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId($incrementId);

        return $order;
    }

    /**
     * get an order based on id
     * @param  int $id
     * @return object
     */
    protected function getOrderById($id)
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->load($id);

        return $order;
    }

    /**
     * get information parameters
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
     * create an Invoice
     * @return void
     */
    public function createInvoice()
    {
        $invoiceService = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService');

        $invoice = $invoiceService->prepareInvoice($this->order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(false);
        $invoice->getOrder()->setIsInProcess(true);

        $transactionSave = $this->_objectManager->create('Magento\Framework\DB\Transaction');
        $transactionSave->addObject($invoice)->addObject($invoice->getOrder())->save();

        $invoiceSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
        $invoiceSender->send($invoice);
    }

    /**
     * save an order additional information
     * @param  array $paymentStatus
     * @return void
     */
    public function saveOrderAdditionalInformation($paymentStatus)
    {
        $payment = $this->order->getPayment();
        $paymentMethod = $this->paymentMethod->getCode();

        $this->setDDAdditionalInformation($paymentMethod, $payment);
        $this->setEasyCreditAdditionalInformation($paymentMethod, $payment, $paymentStatus);

        if (isset($paymentStatus['merchantTransactionId'])) {
            $payment->setAdditionalInformation('TRANSACTION_ID', $paymentStatus['merchantTransactionId']);
        }
        if (isset($paymentStatus['id'])) {
            $payment->setAdditionalInformation('REFERENCE_ID', $paymentStatus['id']);
        }
        if (isset($paymentStatus['currency'])) {
            $payment->setAdditionalInformation('CURRENCY', $paymentStatus['currency']);
        }
        if (isset($paymentStatus['amount'])) {
            $payment->setAdditionalInformation('AMOUNT', $paymentStatus['amount']);
        }
        if (isset($paymentStatus['paymentType'])) {
            $payment->setAdditionalInformation('PAYMENT_TYPE', $paymentStatus['paymentType']);
        }
        if (isset($paymentStatus['result']['code'])) {
            $payment->setAdditionalInformation('RESULT_CODE', $paymentStatus['result']['code']);
        }
        if (isset($paymentStatus['result']['description'])) {
            $payment->setAdditionalInformation('RESULT_DESCRIPTION', $paymentStatus['result']['description']);
        }
        if (isset($paymentStatus['risk']['score'])) {
            $payment->setAdditionalInformation('RISK_SCORE', $paymentStatus['risk']['score']);
        }
        if (isset($paymentStatus['paymentType']) && isset($paymentStatus['result']['code'])) {
            $orderStatusCode = $this->setOrderStatusCode($paymentStatus);
            $payment->setAdditionalInformation('ORDER_STATUS_CODE', $orderStatusCode);
        }

        $this->order->save();
    }

    /**
     * set the Direct Debit Additional Information
     * @param string $paymentMethod
     * @param object $payment
     */
    public function setDDAdditionalInformation($paymentMethod, $payment)
    {
        if ($paymentMethod == 'vrpayecommerce_directdebit' || $paymentMethod == 'vrpayecommerce_ddsaved') {
            if (!$this->paymentMethod->isAdminLoggedIn()) {
                $payment->setAdditionalInformation('MANDATE_ID', $this->checkoutSession->getMandateId());
                $payment->setAdditionalInformation('MANDATE_DATE', $this->checkoutSession->getMandateDate());
            }
        }
    }

    /**
     * set the Direct Debit Additional Information
     * @param string $paymentMethod
     * @param object $payment
     * @param array $paymentStatus
     */
    public function setEasyCreditAdditionalInformation($paymentMethod, $payment, $paymentStatus)
    {
        if ($paymentMethod == 'vrpayecommerce_easycredit') {
            if (isset($paymentStatus['resultDetails']['tilgungsplanText'])) {
                $payment->setAdditionalInformation(
                    'redemption_plan',
                    $paymentStatus['resultDetails']['tilgungsplanText']
                );
            }
            if (isset($paymentStatus['resultDetails']['vorvertraglicheInformationen'])) {
                $payment->setAdditionalInformation(
                    'pre_contract_information_url',
                    $paymentStatus['resultDetails']['vorvertraglicheInformationen']
                );
            }
            if (isset($paymentStatus['resultDetails']['ratenplan.zinsen.anfallendeZinsen'])) {
                $payment->setAdditionalInformation(
                    'easycredit_sum_of_interest',
                    $paymentStatus['resultDetails']['ratenplan.zinsen.anfallendeZinsen']
                );
            }
            if (isset($paymentStatus['resultDetails']['ratenplan.gesamtsumme'])) {
                $payment->setAdditionalInformation(
                    'easycredit_order_total',
                    $paymentStatus['resultDetails']['ratenplan.gesamtsumme']
                );
            }
            if (isset($paymentStatus['customParameters']['orderId'])) {
                $payment->setAdditionalInformation(
                    'easycredit_order_id',
                    $paymentStatus['customParameters']['orderId']
                );
            }
        }
    }

    /**
     * set an order status code
     * @param array $paymentStatus
     */
    protected function setOrderStatusCode($paymentStatus)
    {
        $isInReview = $this->helperPayment->isSuccessReview($paymentStatus['result']['code']);

        if ($isInReview) {
            return 'IR';
        } else {
            return $paymentStatus['paymentType'];
        }
    }

    /**
     * redirect to error page when update a payment status
     * @param  string  $errorMessage
     * @param  string  $orderId
     * @param  boolean|string $detailError
     * @param  string  $url
     * @return void
     */
    public function redirectErrorOrderDetail(
        $errorMessage,
        $orderId,
        $detailError = false,
        $url = 'sales/order/view'
    ) {
        $this->messageManager->addError(__($errorMessage, $orderId, $detailError));
        $this->_redirect($url, ['order_id' => (int)$orderId]);
    }

    /**
     * redirect to success page when update a payment status
     * @param  string $successIdentifier
     * @param  string $orderId
     * @param  string $url
     * @return void
     */
    public function redirectSuccessOrderDetail($successIdentifier, $orderId, $url = 'sales/order/view')
    {
        $this->messageManager->addSuccess(__($successIdentifier, $orderId));
        $this->_redirect($url, ['order_id' => (int)$orderId]);
    }

    /**
     * get error details from easycredit
     *
     * @param array $paymentResponse
     * @return array
     */
    public function getEasyCreditErrorDetail($paymentResponse)
    {
        $errorResults = $this->explodeByMultiDelimiter(
            ["{", "}"],
            $paymentResponse['resultDetails']['Error']
        );
        $errorResults = explode(", ", $errorResults[1]);
        foreach ($errorResults as $errorResult) {
            $errorResultValue = explode("=", $errorResult);
            $easyCreditErrorDetail[$errorResultValue[0]] =
                sizeof($errorResultValue) > 1 ? trim($errorResultValue[1], "'") : trim($errorResultValue[0], "'");
        }
        return $easyCreditErrorDetail;
    }

    /**
     * explode string with multi delimiter
     *
     * @param array $delimiters
     * @param string $string
     * @return array
     */
    public function explodeByMultiDelimiter($delimiters, $string)
    {
        $string = str_replace($delimiters, $delimiters[0], $string);
        $explodedString = explode($delimiters[0], $string);
        return $explodedString;
    }
}
