<?php

namespace Vrpayecommerce\Vrpayecommerce\Helper;

class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $checkoutUrlLive = 'https://vr-pay-ecommerce.de/v1/checkouts';
    protected $checkoutUrlTest = 'https://test.vr-pay-ecommerce.de/v1/checkouts';

    protected $paymentUrlLive = 'https://vr-pay-ecommerce.de/v1/payments';
    protected $paymentUrlTest = 'https://test.vr-pay-ecommerce.de/v1/payments';

    protected $paymentWidgetUrlLive = 'https://vr-pay-ecommerce.de/v1/paymentWidgets.js?checkoutId=';
    protected $paymentWidgetUrlTest = 'https://test.vr-pay-ecommerce.de/v1/paymentWidgets.js?checkoutId=';

    protected $registerUrlLive = 'https://vr-pay-ecommerce.de/v1/registrations/';
    protected $registerUrlTest = 'https://test.vr-pay-ecommerce.de/v1/registrations/';

    protected $queryUrlLive = 'https://vr-pay-ecommerce.de/v1/query';
    protected $queryUrlTest = 'https://test.vr-pay-ecommerce.de/v1/query';

    protected $curl;
    protected $logger;

    protected $ackReturnCodes =  [
        '000.000.000',
        '000.100.110',
        '000.100.111',
        '000.100.112',
        '000.100.200',
        '000.100.201',
        '000.100.202',
        '000.100.203',
        '000.100.204',
        '000.100.205',
        '000.100.206',
        '000.100.207',
        '000.100.208',
        '000.100.209',
        '000.100.210',
        '000.100.220',
        '000.100.221',
        '000.100.222',
        '000.100.223',
        '000.100.224',
        '000.100.225',
        '000.100.226',
        '000.100.227',
        '000.100.228',
        '000.100.229',
        '000.100.230',
        '000.100.299',
        '000.200.000',
        '000.300.000',
        '000.300.100',
        '000.300.101',
        '000.300.102',
        '000.400.000',
        '000.400.010',
        '000.400.020',
        '000.400.030',
        '000.400.040',
        '000.400.050',
        '000.400.060',
        '000.400.070',
        '000.400.080',
        '000.400.090',
        '000.400.101',
        '000.400.102',
        '000.400.103',
        '000.400.104',
        '000.400.105',
        '000.400.106',
        '000.400.107',
        '000.400.108',
        '000.400.200',
        '000.500.000',
        '000.500.100',
        '000.600.000'
    ];

    protected $pendingReturnCodes =  [
        '000.200.000',
        '000.200.100',
        '000.200.101',
        '000.200.102',
        '000.200.200'
    ];
    protected $nokReturnCodes =  [
        '100.100.100',
        '100.100.101',
        '100.100.200',
        '100.100.201',
        '100.100.300',
        '100.100.301',
        '100.100.303',
        '100.100.304',
        '100.100.400',
        '100.100.401',
        '100.100.402',
        '100.100.500',
        '100.100.501',
        '100.100.600',
        '100.100.601',
        '100.100.650',
        '100.100.651',
        '100.100.700',
        '100.100.701',
        '100.150.100',
        '100.150.101',
        '100.150.200',
        '100.150.201',
        '100.150.202',
        '100.150.203',
        '100.150.204',
        '100.150.205',
        '100.150.300',
        '100.200.100',
        '100.200.103',
        '100.200.104',
        '100.200.200',
        '100.210.101',
        '100.210.102',
        '100.211.101',
        '100.211.102',
        '100.211.103',
        '100.211.104',
        '100.211.105',
        '100.211.106',
        '100.212.101',
        '100.212.102',
        '100.212.103',
        '100.250.100',
        '100.250.105',
        '100.250.106',
        '100.250.107',
        '100.250.110',
        '100.250.111',
        '100.250.120',
        '100.250.121',
        '100.250.122',
        '100.250.123',
        '100.250.124',
        '100.250.125',
        '100.250.250',
        '100.300.101',
        '100.300.200',
        '100.300.300',
        '100.300.400',
        '100.300.401',
        '100.300.402',
        '100.300.501',
        '100.300.600',
        '100.300.601',
        '100.300.700',
        '100.300.701',
        '100.350.100',
        '100.350.101',
        '100.350.200',
        '100.350.201',
        '100.350.301',
        '100.350.302',
        '100.350.303',
        '100.350.310',
        '100.350.311',
        '100.350.312',
        '100.350.313',
        '100.350.314',
        '100.350.315',
        '100.350.400',
        '100.350.500',
        '100.350.600',
        '100.350.601',
        '100.350.610',
        '100.360.201',
        '100.360.300',
        '100.360.303',
        '100.360.400',
        '100.370.100',
        '100.370.101',
        '100.370.102',
        '100.370.110',
        '100.370.111',
        '100.370.121',
        '100.370.122',
        '100.370.123',
        '100.370.124',
        '100.370.125',
        '100.370.131',
        '100.370.132',
        '100.380.100',
        '100.380.101',
        '100.380.110',
        '100.380.201',
        '100.380.305',
        '100.380.306',
        '100.380.401',
        '100.380.501',
        '100.390.101',
        '100.390.102',
        '100.390.103',
        '100.390.104',
        '100.390.105',
        '100.390.106',
        '100.390.107',
        '100.390.108',
        '100.390.109',
        '100.390.110',
        '100.390.111',
        '100.390.112',
        '100.390.113',
        '100.395.101',
        '100.395.102',
        '100.395.501',
        '100.395.502',
        '100.396.101',
        '100.396.102',
        '100.396.103',
        '100.396.104',
        '100.396.106',
        '100.396.201',
        '100.397.101',
        '100.397.102',
        '100.400.000',
        '100.400.001',
        '100.400.002',
        '100.400.005',
        '100.400.007',
        '100.400.020',
        '100.400.021',
        '100.400.030',
        '100.400.039',
        '100.400.040',
        '100.400.041',
        '100.400.042',
        '100.400.043',
        '100.400.044',
        '100.400.045',
        '100.400.051',
        '100.400.060',
        '100.400.061',
        '100.400.063',
        '100.400.064',
        '100.400.065',
        '100.400.071',
        '100.400.080',
        '100.400.081',
        '100.400.083',
        '100.400.084',
        '100.400.085',
        '100.400.086',
        '100.400.087',
        '100.400.091',
        '100.400.100',
        '100.400.120',
        '100.400.121',
        '100.400.122',
        '100.400.123',
        '100.400.130',
        '100.400.139',
        '100.400.140',
        '100.400.141',
        '100.400.142',
        '100.400.143',
        '100.400.144',
        '100.400.145',
        '100.400.146',
        '100.400.147',
        '100.400.148',
        '100.400.149',
        '100.400.150',
        '100.400.151',
        '100.400.152',
        '100.400.241',
        '100.400.242',
        '100.400.243',
        '100.400.260',
        '100.400.300',
        '100.400.301',
        '100.400.302',
        '100.400.303',
        '100.400.304',
        '100.400.305',
        '100.400.306',
        '100.400.307',
        '100.400.308',
        '100.400.309',
        '100.400.310',
        '100.400.311',
        '100.400.312',
        '100.400.313',
        '100.400.314',
        '100.400.315',
        '100.400.316',
        '100.400.317',
        '100.400.318',
        '100.400.319',
        '100.400.320',
        '100.400.321',
        '100.400.322',
        '100.400.323',
        '100.400.324',
        '100.400.325',
        '100.400.326',
        '100.400.327',
        '100.400.328',
        '100.400.500',
        '100.500.101',
        '100.500.201',
        '100.500.301',
        '100.500.302',
        '100.550.300',
        '100.550.301',
        '100.550.303',
        '100.550.310',
        '100.550.311',
        '100.550.312',
        '100.550.400',
        '100.550.401',
        '100.550.601',
        '100.550.603',
        '100.550.605',
        '100.600.500',
        '100.700.100',
        '100.700.101',
        '100.700.200',
        '100.700.201',
        '100.700.300',
        '100.700.400',
        '100.700.500',
        '100.700.800',
        '100.700.801',
        '100.700.802',
        '100.700.810',
        '100.800.100',
        '100.800.101',
        '100.800.102',
        '100.800.200',
        '100.800.201',
        '100.800.202',
        '100.800.300',
        '100.800.301',
        '100.800.302',
        '100.800.400',
        '100.800.401',
        '100.800.500',
        '100.800.501',
        '100.900.100',
        '100.900.101',
        '100.900.105',
        '100.900.200',
        '100.900.300',
        '100.900.301',
        '100.900.400',
        '100.900.401',
        '100.900.450',
        '100.900.500',
        '200.100.101',
        '200.100.102',
        '200.100.103',
        '200.100.150',
        '200.100.151',
        '200.100.199',
        '200.100.201',
        '200.100.300',
        '200.100.301',
        '200.100.302',
        '200.100.401',
        '200.100.402',
        '200.100.403',
        '200.100.404',
        '200.100.501',
        '200.100.502',
        '200.100.503',
        '200.100.504',
        '200.200.106',
        '200.300.403',
        '200.300.404',
        '200.300.405',
        '200.300.406',
        '200.300.407',
        '500.100.201',
        '500.100.202',
        '500.100.203',
        '500.100.301',
        '500.100.302',
        '500.100.303',
        '500.100.304',
        '500.100.401',
        '500.100.402',
        '500.100.403',
        '500.200.101',
        '600.100.100',
        '600.200.100',
        '600.200.200',
        '600.200.201',
        '600.200.202',
        '600.200.300',
        '600.200.310',
        '600.200.400',
        '600.200.500',
        '600.200.600',
        '600.200.700',
        '600.200.800',
        '600.200.810',
        '700.100.100',
        '700.100.200',
        '700.100.300',
        '700.100.400',
        '700.100.500',
        '700.100.600',
        '700.100.700',
        '700.100.701',
        '700.100.710',
        '700.300.100',
        '700.300.200',
        '700.300.300',
        '700.300.400',
        '700.300.500',
        '700.300.600',
        '700.300.700',
        '700.400.000',
        '700.400.100',
        '700.400.101',
        '700.400.200',
        '700.400.300',
        '700.400.400',
        '700.400.402',
        '700.400.410',
        '700.400.420',
        '700.400.510',
        '700.400.520',
        '700.400.530',
        '700.400.540',
        '700.400.550',
        '700.400.560',
        '700.400.561',
        '700.400.562',
        '700.400.570',
        '700.400.700',
        '700.450.001',
        '800.100.100',
        '800.100.150',
        '800.100.151',
        '800.100.152',
        '800.100.153',
        '800.100.154',
        '800.100.155',
        '800.100.156',
        '800.100.157',
        '800.100.158',
        '800.100.159',
        '800.100.160',
        '800.100.161',
        '800.100.162',
        '800.100.163',
        '800.100.164',
        '800.100.165',
        '800.100.166',
        '800.100.167',
        '800.100.168',
        '800.100.169',
        '800.100.170',
        '800.100.171',
        '800.100.172',
        '800.100.173',
        '800.100.174',
        '800.100.175',
        '800.100.176',
        '800.100.177',
        '800.100.178',
        '800.100.179',
        '800.100.190',
        '800.100.191',
        '800.100.192',
        '800.100.195',
        '800.100.196',
        '800.100.197',
        '800.100.198',
        '800.100.402',
        '800.100.500',
        '800.100.501',
        '800.110.100',
        '800.120.100',
        '800.120.101',
        '800.120.102',
        '800.120.103',
        '800.120.200',
        '800.120.201',
        '800.120.202',
        '800.120.203',
        '800.120.300',
        '800.120.401',
        '800.121.100',
        '800.130.100',
        '800.140.100',
        '800.140.101',
        '800.140.110',
        '800.140.111',
        '800.140.112',
        '800.140.113',
        '800.150.100',
        '800.160.100',
        '800.160.110',
        '800.160.120',
        '800.160.130',
        '800.200.159',
        '800.200.160',
        '800.200.165',
        '800.200.202',
        '800.200.208',
        '800.200.220',
        '800.300.101',
        '800.300.102',
        '800.300.200',
        '800.300.301',
        '800.300.302',
        '800.300.401',
        '800.300.500',
        '800.300.501',
        '800.400.100',
        '800.400.101',
        '800.400.102',
        '800.400.103',
        '800.400.104',
        '800.400.105',
        '800.400.110',
        '800.400.150',
        '800.400.151',
        '800.400.200',
        '800.400.500',
        '800.500.100',
        '800.500.110',
        '800.600.100',
        '800.700.100',
        '800.700.101',
        '800.700.201',
        '800.700.500',
        '800.800.102',
        '800.800.202',
        '800.800.302',
        '800.800.800',
        '800.800.801',
        '800.900.100',
        '800.900.101',
        '800.900.200',
        '800.900.201',
        '800.900.300',
        '800.900.301',
        '800.900.302',
        '800.900.303',
        '800.900.401',
        '800.900.450',
        '900.100.100',
        '900.100.200',
        '900.100.201',
        '900.100.202',
        '900.100.203',
        '900.100.300',
        '900.100.400',
        '900.100.500',
        '900.100.600',
        '900.200.100',
        '900.300.600',
        '900.400.100',
        '999.999.999'
    ];

    /**
     * [__construct description]
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Vrpayecommerce\Vrpayecommerce\Helper\Curl $curl,
        \Vrpayecommerce\Vrpayecommerce\Helper\Logger $logger
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->logger = $logger;
    }

    /**
     * OPP URL
     *
     */

    /**
     * Get the URL to checkout payment based on the server mode
     * @param  string $serverMode
     * @return string
     */
    protected function getCheckoutUrl($serverMode)
    {
        if ($serverMode == 'LIVE') {
            return $this->checkoutUrlLive;
        } else {
            return $this->checkoutUrlTest;
        }
    }

    /**
     * Get the payment status URL to receive responses from the payment gateway
     * @param  string $serverMode
     * @param  string $checkoutId
     * @return string
     */
    public function getPaymentStatusUrl($serverMode, $checkoutId)
    {
        if ($serverMode == 'LIVE') {
            return $this->checkoutUrlLive . '/' . $checkoutId. '/payment';
        } else {
            return $this->checkoutUrlTest . '/' . $checkoutId. '/payment';
        }
    }

    /**
     * Get the back office URL based on the server mode
     * @param  string $serverMode
     * @param  string $referenceId
     * @return string
     */
    protected function getBackOfficeUrl($serverMode, $referenceId)
    {
        if ($serverMode == 'LIVE') {
            return $this->paymentUrlLive . '/' . $referenceId;
        } else {
            return $this->paymentUrlTest . '/' . $referenceId;
        }
    }

     /**
      * Get the URL to use a registered account
      * @param  string $serverMode
      * @param  string $referenceId
      * @return string
      */
    protected function getUrlToUseRegisteredAccount($serverMode, $referenceId)
    {
        if ($serverMode == 'LIVE') {
            return $this->registerUrlLive. $referenceId . '/payments';
        } else {
            return $this->registerUrlTest. $referenceId . '/payments';
        }
    }

    /**
     * Get the deregister URL based on the server mode
     * @param  string $serverMode
     * @param  string $referenceId
     * @return string
     */
    protected function getDeRegisterUrl($serverMode, $referenceId)
    {
        if ($serverMode == 'LIVE') {
            return $this->registerUrlLive. $referenceId;
        } else {
            return $this->registerUrlTest. $referenceId;
        }
    }

    /**
     * Get the query URL based on the server mode
     * @param  string $serverMode
     * @return string
     */
    public function getQueryUrl($serverMode, $referenceId)
    {
        if ($serverMode == 'LIVE') {
            return $this->queryUrlLive. '/' .$referenceId;
        } else {
            return $this->queryUrlTest. '/' .$referenceId;
        }
    }

    /**
     * get the server to server payment URL
     * @param  string $serverMode
     * @return void
     */
    public function getServerToServerInitialPaymentUrl($serverMode)
    {
        if ($serverMode == 'LIVE') {
            return $this->paymentUrlLive;
        } else {
            return $this->paymentUrlTest;
        }
    }

    /**
     * OPP Post Parameter
     *
     */

    /**
     * Get the payment credential to send request to the gateway
     * @param  array $order
     * @return array
     */
    public function getPaymentCredential($order)
    {
        $paymentCredential = [];
        if(empty($order['bearerToken']))
        {
            $paymentCredential['authentication.userId'] = $order['login'];
            $paymentCredential['authentication.password'] = $order['password'];
            $paymentCredential['authentication.entityId'] = $order['channelId'];
        }
        else
        {
            $paymentCredential['entityId'] = $order['channelId'];
        }

        // test mode parameters (true)
        if (!empty($order['testMode'])) {
            $paymentCredential['testMode'] = $order['testMode'];
        }
        return $paymentCredential;
    }

    /**
     * Set the separator for the decimal point
     * Set the number of decimal points
     * @param string|float $number
     * @return string
     */
    public function setNumberFormat($number)
    {
        $number = (float) str_replace(',', '.', $number);
        return number_format($number, 2, '.', '');
    }

    /**
     * set customer parameters
     * @param  array $order
     * @return array
     */
    public function setCustomerParameters($order)
    {
        $customerParameters = [];

        $customerParameters['customer.email'] = $order['customer']['email'];
        $customerParameters['customer.givenName'] = $order['customer']['firstName'];
        $customerParameters['customer.surname'] = $order['customer']['lastName'];

        if (!empty($order['customer']['sex'])) {
            $customerParameters['customer.sex'] = $order['customer']['sex'];
        }
        if (!empty($order['customer']['birthdate'])
        && $order['customer']['birthdate'] != '0000-00-00') {
            $customerParameters['customer.birthDate'] = $order['customer']['birthdate'];
        }
        if (!empty($order['customer']['phone'])) {
            $customerParameters['customer.phone'] = $order['customer']['phone'];
        }

        return $customerParameters;
    }

    /**
     * set billing address parameters
     * @param array $order
     */
    public function setBillingAddressParameters($order)
    {
        $billingAddressParameters = [];

        if (!empty($order['billing']['street'])) {
            $billingAddressParameters['billing.street1'] = $order['billing']['street'];
        }
        if (!empty($order['billing']['city'])) {
            $billingAddressParameters['billing.city'] = $order['billing']['city'];
        }
        if (!empty($order['billing']['state'])) {
            $billingAddressParameters['billing.state'] = $order['billing']['state'];
        }
        if (!empty($order['billing']['zip'])) {
            $billingAddressParameters['billing.postcode'] = $order['billing']['zip'];
        }
        if (!empty($order['billing']['countryCode'])) {
            $billingAddressParameters['billing.country'] = $order['billing']['countryCode'];
        }

        return $billingAddressParameters;
    }

    /**
     * set shipping address parameters
     * @param array $order
     */
    public function setShippingAddressParameters($order)
    {
        $shippingAddressParameters = [];

        if (!empty($order['shipping']['street'])) {
            $shippingAddressParameters['shipping.street1'] = $order['shipping']['street'];
        }
        if (!empty($order['shipping']['city'])) {
            $shippingAddressParameters['shipping.city'] = $order['shipping']['city'];
        }
        if (!empty($order['shipping']['state'])) {
            $shippingAddressParameters['shipping.state'] = $order['shipping']['state'];
        }
        if (!empty($order['shipping']['zip'])) {
            $shippingAddressParameters['shipping.postcode'] = $order['shipping']['zip'];
        }
        if (!empty($order['shipping']['countryCode'])) {
            $shippingAddressParameters['shipping.country'] = $order['shipping']['countryCode'];
        }

        return $shippingAddressParameters;
    }

    /**
     * get credit cards 3D parameters
     * @param  array $order
     * @return array
     */
    public function getCC3DParameters($order)
    {
        $cc3DParameters = [];

        if (!empty($order['createRegistration']) && !empty($order['3D'])) {
            $cc3DParameters['customParameters[presentation.amount3D]'] =
                $this->setNumberFormat($order['3D']['amount']);
            $cc3DParameters['customParameters[presentation.currency3D]'] = $order['3D']['currency'];
        }

        return $cc3DParameters;
    }

    /**
     * get cart items parameters
     * @param  array $order
     * @return array
     */
    public function getCartItemsParameters($order)
    {
        $cartItemsParameters = [];

        if (!empty($order['cartItems'])) {
            foreach ($order['cartItems'] as $key => $value) {
                $cartItemsParameters['cart.items['.$key.'].merchantItemId'] = $value['merchantItemId'];
                $cartItemsParameters['cart.items['.$key.'].quantity'] = $value['quantity'];
                $cartItemsParameters['cart.items['.$key.'].name'] = $value['name'];
                $cartItemsParameters['cart.items['.$key.'].price'] = $this->setNumberFormat($value['price']);
                foreach(['discount', 'tax', 'totalAmount', 'totalTaxAmount'] as $additionalArgument)
                {
                    if(isset($value[$additionalArgument]))
                    {
                        $cartItemsParameters['cart.items['.$key.'].' . $additionalArgument] = $this->setNumberFormat($value[$additionalArgument]);
                    }
                }
            }
        }
        return $cartItemsParameters;
    }

    /**
     * get klarna parameters
     * @param  array $order
     * @return array
     */
    public function getKlarnaParameters($order)
    {
        $klarnaParameters = [];

        if (!empty($order['customParameters']['klarnaCartItem1Flags'])) {
            $klarnaParameters['customParameters[KLARNA_CART_ITEM1_FLAGS]'] =
                $order['customParameters']['klarnaCartItem1Flags'];
        }
        if (!empty($order['customParameters']['klarnaPclassFlag'])
            && trim($order['customParameters']['klarnaPclassFlag'])!=='') {
            $klarnaParameters['customParameters[KLARNA_PCLASS_FLAG]'] =
                $order['customParameters']['klarnaPclassFlag'];
        }

        return $klarnaParameters;
    }

    /**
     * get easyCredit parameters
     *
     * @param array $order
     * @return array
     */
    public function getEasyCreditParameters($order)
    {
        $easyCreditParameters = [];

        if (isset($order['customParameters']['RISK_ANZAHLBESTELLUNGEN'])) {
            $easyCreditParameters['customParameters[RISK_ANZAHLBESTELLUNGEN]'] =
                $order['customParameters']['RISK_ANZAHLBESTELLUNGEN'];
        }
        if (isset($order['customParameters']['RISK_BESTELLUNGERFOLGTUEBERLOGIN'])) {
            $easyCreditParameters['customParameters[RISK_BESTELLUNGERFOLGTUEBERLOGIN]'] =
                $order['customParameters']['RISK_BESTELLUNGERFOLGTUEBERLOGIN'];
        }
        if (isset($order['customParameters']['RISK_KUNDENSTATUS'])) {
            $easyCreditParameters['customParameters[RISK_KUNDENSTATUS]'] =
                $order['customParameters']['RISK_KUNDENSTATUS'];
        }
        if (isset($order['customParameters']['RISK_KUNDESEIT'])) {
            $easyCreditParameters['customParameters[RISK_KUNDESEIT]'] =
                $order['customParameters']['RISK_KUNDESEIT'];
        }

        return $easyCreditParameters;
    }

    /**
     * get paydirekt parameters
     * @param  array $order
     * @return array
     */
    public function getPaydirektParameters($order)
    {
        $paydirektParameters = [];

        if (!empty($order['customParameters']['paydirektMinimumAge'])) {
            $paydirektParameters['customParameters[PAYDIREKT_minimumAge]'] =
                $order['customParameters']['paydirektMinimumAge'];
        }
        if (!empty($order['customParameters']['paydirektPaymentIsPartial'])) {
            $paydirektParameters['customParameters[PAYDIREKT_payment.isPartial]'] =
                $order['customParameters']['paydirektPaymentIsPartial'];
        }
        if (!empty($order['customParameters']['paydirektPaymentShippingAmount'])) {
            $paydirektParameters['customParameters[PAYDIREKT_payment.shippingAmount]'] =
                $this->setNumberFormat($order['customParameters']['paydirektPaymentShippingAmount']);
        }

        return $paydirektParameters;
    }

    /**
     * get payment transaction parameters
     * @param  array $order
     * @return array
     */
    public function getPaymentTransactionParameters($order)
    {
        $paymentTransactionParameters = [];

        if (!empty($order['customParameters']['orderId'])) {
            $paymentTransactionParameters['customParameters[orderId]'] =
                $order['customParameters']['orderId'];
        }
        if (!empty($order['customParameters']['paymentMethod'])) {
            $paymentTransactionParameters['customParameters[paymentMethod]'] =
                $order['customParameters']['paymentMethod'];
        }
        $paymentTransactionParameters['merchantTransactionId'] = $order['transactionId'];
        $paymentTransactionParameters['amount'] = $this->setNumberFormat($order['amount']);
        $paymentTransactionParameters['currency'] = $order['currency'];

        if (!empty($order['paymentBrand'])) {
            $paymentTransactionParameters['paymentBrand'] = $order['paymentBrand'];
        }
        // payment type for RG.DB or only RG
        if (!empty($order['paymentType'])) {
            $paymentTransactionParameters['paymentType'] = $order['paymentType'];
        }
        // registration parameter : true
        if (!empty($order['createRegistration'])) {
            $paymentTransactionParameters['createRegistration'] = $order['createRegistration'];
        }
        // recurring payment parameters : initial/repeated
        if (!empty($order['recurringType'])) {
            $paymentTransactionParameters['recurringType'] = $order['recurringType'];
        }
        if (!empty($order['registrations'])) {
            foreach ($order['registrations'] as $key => $value) {
                $paymentTransactionParameters['registrations['.$key.'].id'] = $value;
            }
        }
        if (!empty($order['shopperResultUrl'])) {
            $paymentTransactionParameters['shopperResultUrl'] = $order['shopperResultUrl'];
        }

        return $paymentTransactionParameters;
    }

    /**
     * get custom parameters
     * @param  array $order
     * @return array
     */
    public function getCustomParameters($order)
    {
        $customParameters = [];

        if (!empty($order['customParameters']['SHOP_VERSION'])) {
            $customParameters['customParameters[SHOP_VERSION]'] =
                $order['customParameters']['SHOP_VERSION'];
        }
        if (!empty($order['customParameters']['PLUGIN_VERSION'])) {
            $customParameters['customParameters[PLUGIN_VERSION]'] =
                $order['customParameters']['PLUGIN_VERSION'];
        }

        if (!empty($order['customParameters']['buyerCompanyVat']))
        {
            $customParameters['customParameters[buyerCompanyVat]'] = $order['customParameters']['buyerCompanyVat'];
        }

        // custom parameters for new version tracker
        if (!empty($order['customParameters']['PLUGIN_accountId']))
        {
            $customParameters['customParameters[PLUGIN_accountId]'] = $order['customParameters']['PLUGIN_accountId'];
        }
        if (!empty($order['customParameters']['PLUGIN_shopUrl']))
        {
            $customParameters['customParameters[PLUGIN_shopUrl]'] = $order['customParameters']['PLUGIN_shopUrl'];
        }
        if (!empty($order['customParameters']['PLUGIN_shopSystem']))
        {
            $customParameters['customParameters[PLUGIN_shopSystem]'] = $order['customParameters']['PLUGIN_shopSystem'];
        }
        if (!empty($order['customParameters']['PLUGIN_shopSystemVersion']))
        {
            $customParameters['customParameters[PLUGIN_shopSystemVersion]'] = $order['customParameters']['PLUGIN_shopSystemVersion'];
        }
        if (!empty($order['customParameters']['PLUGIN_version']))
        {
            $customParameters['customParameters[PLUGIN_version]'] = $order['customParameters']['PLUGIN_version'];
        }
        if (!empty($order['customParameters']['PLUGIN_outletLocation']))
        {
            $customParameters['customParameters[PLUGIN_outletLocation]'] = $order['customParameters']['PLUGIN_outletLocation'];
        }
        if (!empty($order['customParameters']['PLUGIN_mode']))
        {
            $customParameters['customParameters[PLUGIN_mode]'] = $order['customParameters']['PLUGIN_mode'];
        }

        return $customParameters;
    }

    /**
     * Set checkout parameters
     * @param array $order
     * @return string
     */
    public function setPostData($order)
    {
        $checkoutParameters = [];

        $checkoutParameters = array_merge(
            $this->getPaymentCredential($order),
            $this->setCustomerParameters($order),
            $this->setBillingAddressParameters($order),
            $this->setShippingAddressParameters($order),
            $this->getCC3DParameters($order),
            $this->getKlarnaParameters($order),
            $this->getEasyCreditParameters($order),
            $this->getCartItemsParameters($order),
            $this->getPaydirektParameters($order),
            $this->getPaymentTransactionParameters($order),
            $this->getCustomParameters($order)
        );

        return http_build_query($checkoutParameters, '', '&');
    }

    /**
     * Set parameters to use registered payment account
     * @param array $order
     * @return string
     */
    public function setRegisteredAccountParameters($order)
    {
        $registrationParameters = [];
        $registrationParameters = array_merge(
            $this->getPaymentCredential($order),
            $this->getCustomParameters($order)
        );
        $registrationParameters['amount'] = $this->setNumberFormat($order['amount']);
        $registrationParameters['currency'] = $order['currency'];
        $registrationParameters['paymentType'] = $order['paymentType'];
        $registrationParameters['merchantTransactionId'] = $order['transactionId'];
        $registrationParameters['recurringType'] = $order['recurringType'];

        return http_build_query($registrationParameters, '', '&');
    }

    /**
     * Set back office parameters
     * @param array $order
     * @return string
     */
    public function setBackOfficeParameter($order)
    {
        $backOfficeParameters = [];
        $backOfficeParameters = $this->getPaymentCredential($order);
        $backOfficeParameters['paymentType'] = $order['paymentType'];

        //Reversal (RV) does not send amount & currency parameter
        if ($order['paymentType'] == 'RF' || $order['paymentType'] == 'CP') {
            $backOfficeParameters['amount'] = $this->setNumberFormat($order['amount']);
            $backOfficeParameters['currency'] = $order['currency'];
        }

        return http_build_query($backOfficeParameters, '', '&');
    }

    /**
     * get a payment mode
     * @param  string $testMode
     * @return string
     */
    public function getPaymentMode($testMode)
    {
        switch ($testMode) {
            case 'INTERNAL':
                return 'INTEGRATOR_TEST';
            case 'EXTERNAL':
                return 'CONNECTOR_TEST';
            default:
                return 'LIVE';
        }
    }

    /**
     * OPP Main Operation
     *
     */

    /**
     * Check if the plugin gets a response from the gateway in 3 trials
     * @param  string  $paymentStatusUrl
     * @param  array  &$resultJson
     * @param  string  $proxyParameters
     * @param  string  $serverMode
     * @param  string  $bearerToken
     * @return array
     */
    public function isPaymentGetResponse($paymentStatusUrl, &$resultJson, $proxyParameters, $serverMode, $bearerToken = '')
    {
        $this->logger->addLogVrpayecommerce('start try get the response from gateway');
        for ($i=0; $i < 3; $i++) {
            $response = true;
            try {
                $resultJson = $this->curl->getPaymentResponse(
                    $paymentStatusUrl,
                    $proxyParameters,
                    $serverMode,
                    $bearerToken
                );
            } catch (\Exception $e) {
                $resultJson['response'] = 'ERROR_GENERAL_TIMEOUT';
                $resultJson['isValid'] = false;
                $response = false;
            }
            if ($response && $resultJson) {
                break;
            }
        }
        $this->logger->addLogVrpayecommerce('get response : ', $resultJson);
        $this->logger->addLogVrpayecommerce('end try get the response from gateway');
        return $resultJson;
    }

    /**
     * initialize the server to server a payment
     * @param  array $order
     * @return void
     */
    public function initializeServerToServerPayment($serverToServerParameters)
    {
        $this->logger->addLogVrpayecommerce('start initialize server to server payment');
        $this->logger->addLogVrpayecommerce('get server to server parameters : ', $serverToServerParameters);
        
        $initialPaymentUrl = $this->getServerToServerInitialPaymentUrl($serverToServerParameters['serverMode']);
        $this->logger->addLogVrpayecommerce('get initialize server to server api url : ', $initialPaymentUrl);

        $curlPostData = $this->setPostData($serverToServerParameters);
        $this->logger->addLogVrpayecommerce('get curl post data : ', $curlPostData);
       
        $response = $this->curl->getResponseData(
            $curlPostData,
            $initialPaymentUrl,
            $serverToServerParameters['proxy'],
            $serverToServerParameters['serverMode'],
            $serverToServerParameters['bearerToken']
        );
        $this->logger->addLogVrpayecommerce('get response from gateway : ', $response);
        $this->logger->addLogVrpayecommerce('end initialize server to server payment');
        
        return $response;
    }

    /**
     * Prepare the checkout & get checkout id (for create payment form)
     * @param  array $checkoutParameters
     * @return array $response
     */
    public function getCheckoutResult($checkoutParameters)
    {
        //prepareCheckout
        $this->logger->addLogVrpayecommerce('get checkout parameters : ', $checkoutParameters);

        $checkoutUrl = $this->getCheckoutUrl($checkoutParameters['serverMode']);
        $this->logger->addLogVrpayecommerce('get API url : ', $checkoutUrl);

        $curlPostData = $this->setPostData($checkoutParameters);
        $this->logger->addLogVrpayecommerce('set curl post data : ', $curlPostData);

        $response = $this->curl->getResponseData(
            $curlPostData,
            $checkoutUrl,
            $checkoutParameters['proxy'],
            $checkoutParameters['serverMode'],
            $checkoutParameters['bearerToken']
        );
        $this->logger->addLogVrpayecommerce('get response from gateway : ', $response);

        return $response;
    }

   /**
    * get payment widget URL (for create payment form)
    * @param  string $serverMode
    * @param  string $checkoutId
    * @return string $paymentWidgetUrl
    */
    public function getPaymentWidgetUrl($serverMode, $checkoutId)
    {
        $this->logger->addLogVrpayecommerce('start get payment widget url');

        if ($serverMode == 'LIVE') {
            $paymentWidgetUrl = $this->paymentWidgetUrlLive . $checkoutId;
        } else {
            $paymentWidgetUrl = $this->paymentWidgetUrlTest . $checkoutId;
        }
        $this->logger->addLogVrpayecommerce('payment widget url: ', $paymentWidgetUrl);
        
        $this->logger->addLogVrpayecommerce('end get payment widget url');

        return $paymentWidgetUrl;
    }

    /**
     * Get a Payment Status
     * @param  string $checkoutId
     * @param  array $order
     * @param  boolean $isServerToServer
     * @return string|boolean
     */
    public function getPaymentStatus($checkoutId, $order, $isServerToServer = false)
    {
        $this->logger->addLogVrpayecommerce('start get payment status');
        $this->logger->addLogVrpayecommerce('get chekout id : ', $checkoutId);
        $this->logger->addLogVrpayecommerce('get order data : ', $order);
        $this->logger->addLogVrpayecommerce('is server to server : ', $isServerToServer);
        
        unset($order['testMode']);
        if ($isServerToServer) {
            $paymentStatusUrl = $this->getBackOfficeUrl($order['serverMode'], $checkoutId);
        } else {
            $paymentStatusUrl = $this->getPaymentStatusUrl($order['serverMode'], $checkoutId);
        }
        $paymentStatusUrl .= '?'.http_build_query($this->getPaymentCredential($order), '', '&');
        $this->logger->addLogVrpayecommerce('get API url : ', $paymentStatusUrl);

        $response = $this->isPaymentGetResponse(
            $paymentStatusUrl,
            $resultJson,
            $order['proxy'],
            $order['serverMode'],
            $order['bearerToken']
        );
        $this->logger->addLogVrpayecommerce('get response from gateway : ', $response);
        $this->logger->addLogVrpayecommerce('end get payment status ');
        
        return $response;
    }

    /**
     * Use the registered payment account
     * @param  string $referenceId
     * @param  array $order
     * @return string
     */
    public function useRegisteredAccount($referenceId, $order)
    {
        $this->logger->addLogVrpayecommerce('start get payment status');
        $this->logger->addLogVrpayecommerce('get referenceId : ', $referenceId);
        $this->logger->addLogVrpayecommerce('get order data : ', $order);
        
        $registerUrl = $this->getUrlToUseRegisteredAccount($order['serverMode'], $referenceId);
        $this->logger->addLogVrpayecommerce('get API url : ', $registerUrl);

        $postData = $this->setRegisteredAccountParameters($order);
        $this->logger->addLogVrpayecommerce('get post data : ', $postData);

        $resultJson = $this->curl->getResponseData(
            $postData,
            $registerUrl,
            $order['proxy'],
            $order['serverMode'],
            $order['bearerToken']
        );
        $this->logger->addLogVrpayecommerce('get response from gateway : ', $resultJson);

        return $resultJson;
    }

    /**
     * Check if the plugin gets a paypal response from the gateway in 3 trials
     * @param  string  $referenceId
     * @param  string  $order
     * @return boolean
     */
    public function isDebitPaypalGetResponse($referenceId, $order)
    {
        $this->logger->addLogVrpayecommerce('start try get the response from gateway');
        for ($i=0; $i < 3; $i++) {
            $this->logger->addLogVrpayecommerce('trials '.$i);
            $response = true;
            try {
                $resultJson = $this->useRegisteredAccount($referenceId, $order);
            } catch (\Exception $e) {
                $resultJson['response'] = 'ERROR_GENERAL_TIMEOUT';
                $resultJson['isValid'] = false;
                $response = false;
            }
            if ($response && $resultJson) {
                break;
            }
        }

        $this->logger->addLogVrpayecommerce('get response : ', $resultJson);
        $this->logger->addLogVrpayecommerce('end try get the response from gateway');
        return $resultJson;
    }

    /**
     * Back office operations such ad capture and refund
     * @param  string $referenceId
     * @param  array $order
     * @return string
     */
    public function backOfficeOperation($referenceId, $order)
    {
        $this->logger->addLogVrpayecommerce('start backOfficeOperation');
        $this->logger->addLogVrpayecommerce('get referenceId : ', $referenceId);
        $this->logger->addLogVrpayecommerce('get order data : ', $order);

        $backOfficeUrl = $this->getBackOfficeUrl($order['serverMode'], $referenceId);
        $this->logger->addLogVrpayecommerce('get API url : ', $backOfficeUrl);

        $postData = $this->setBackOfficeParameter($order);
        $this->logger->addLogVrpayecommerce('get postData : ', $postData);
        
        $resultJson = $this->curl->getResponseData(
            $postData,
            $backOfficeUrl,
            $order['proxy'],
            $order['serverMode'],
            $order['bearerToken']
        );
        $this->logger->addLogVrpayecommerce('get response from gateway : ', $resultJson);
        $this->logger->addLogVrpayecommerce('end back office operation');

        return $resultJson;
    }

    /**
     * delete the payment account
     * @param  string $referenceId
     * @param  array $order
     * @return string|boolean
     */
    public function deleteRegistration($referenceId, $order)
    {
        $this->logger->addLogVrpayecommerce('start deregister payment account');
        $this->logger->addLogVrpayecommerce('get referenceId : ', $referenceId);
        $this->logger->addLogVrpayecommerce('get order data : ', $order);

        $deRegisterUrl = $this->getDeRegisterUrl($order['serverMode'], $referenceId);
        $deRegisterUrl .= '?'.http_build_query($this->getPaymentCredential($order), '', '&');
        $this->logger->addLogVrpayecommerce('get API url : ', $deRegisterUrl);

        $resultJson = $this->curl->sendDeRegistration(
            $deRegisterUrl,
            $order['proxy'],
            $order['serverMode'],
            $order['bearerToken']
        );
        $this->logger->addLogVrpayecommerce('get response from gateway : ', $resultJson);
        $this->logger->addLogVrpayecommerce('end deregister payment account');

        return $resultJson;
    }

    /**
     * update payment status
     * @param  string $referenceId
     * @param  array $order
     * @return xml
     */
    public function updateStatus($referenceId, $updateStatusParameters)
    {
        $this->logger->addLogVrpayecommerce('start update payment status');
        $this->logger->addLogVrpayecommerce('get referenceId : ', $referenceId);
        $this->logger->addLogVrpayecommerce('get update status parameters : ', $updateStatusParameters);

        $queryUrl = $this->getQueryUrl($updateStatusParameters['serverMode'], $referenceId);
        unset($updateStatusParameters['testMode']);
        $queryUrl .= '?'.http_build_query($this->getPaymentCredential($updateStatusParameters), '', '&');
        $this->logger->addLogVrpayecommerce('get API url : ', $queryUrl);
        
        $response = $this->curl->getPaymentResponse(
            $queryUrl,
            $updateStatusParameters['proxy'],
            $updateStatusParameters['serverMode'],
            $updateStatusParameters['bearerToken']
        );
        $this->logger->addLogVrpayecommerce('get response from gateway  : ', $response);
        $this->logger->addLogVrpayecommerce('start update payment status');

        return $response;
    }

    /**
     * Get a transaction result (ACK or NOK)
     * @param  boolean $returnCode
     * @return string|boolean
     */
    public function getTransactionResult($returnCode = false)
    {
        if ($returnCode) {
            if (in_array($returnCode, $this->ackReturnCodes)) {
                if (in_array($returnCode, $this->pendingReturnCodes)) {
                    return "PD";
                }
                return "ACK";
            } elseif (in_array($returnCode, $this->nokReturnCodes)) {
                return "NOK";
            }
        }
        return false;
    }

    /**
     * get a risk score payment
     * @param  int $riskScore
     * @return string
     */
    public function getRiskScorePayment($riskScore)
    {
        if ($riskScore >= 0) {
            return 'Success';
        }

        return 'Failed';
    }

    /**
     * Get error identifier
     * @param  string $code
     * @return string
     */
    public function getErrorIdentifier($code)
    {
        $errorMessages = [
            '800.150.100' => 'ERROR_CC_ACCOUNT',

            '800.100.402' => 'ERROR_CC_INVALIDDATA',
            '100.100.101' => 'ERROR_CC_INVALIDDATA',
            '800.100.151' => 'ERROR_CC_INVALIDDATA',
            '000.400.108' => 'ERROR_CC_INVALIDDATA',
            '100.100.100' => 'ERROR_CC_INVALIDDATA',
            '100.100.200' => 'ERROR_CC_INVALIDDATA',
            '100.100.201' => 'ERROR_CC_INVALIDDATA',
            '100.100.300' => 'ERROR_CC_INVALIDDATA',
            '100.100.301' => 'ERROR_CC_INVALIDDATA',
            '100.100.304' => 'ERROR_CC_INVALIDDATA',
            '100.100.400' => 'ERROR_CC_INVALIDDATA',
            '100.100.401' => 'ERROR_CC_INVALIDDATA',
            '100.100.402' => 'ERROR_CC_INVALIDDATA',
            '100.100.651' => 'ERROR_CC_INVALIDDATA',
            '100.100.700' => 'ERROR_CC_INVALIDDATA',
            '100.200.100' => 'ERROR_CC_INVALIDDATA',
            '100.200.103' => 'ERROR_CC_INVALIDDATA',
            '100.200.104' => 'ERROR_CC_INVALIDDATA',
            '100.400.000' => 'ERROR_CC_INVALIDDATA',
            '100.400.001' => 'ERROR_CC_INVALIDDATA',
            '100.400.086' => 'ERROR_CC_INVALIDDATA',
            '100.400.087' => 'ERROR_CC_INVALIDDATA',
            '100.400.002' => 'ERROR_CC_INVALIDDATA',
            '100.400.316' => 'ERROR_CC_INVALIDDATA',
            '100.400.317' => 'ERROR_CC_INVALIDDATA',
            '100.100.600' => 'ERROR_CC_INVALIDDATA',

            '800.300.401' => 'ERROR_CC_BLACKLIST',

            '800.100.171' => 'ERROR_CC_DECLINED_CARD',
            '800.100.165' => 'ERROR_CC_DECLINED_CARD',
            '800.100.159' => 'ERROR_CC_DECLINED_CARD',
            '800.100.195' => 'ERROR_CC_DECLINED_CARD',
            '000.400.101' => 'ERROR_CC_DECLINED_CARD',
            '100.100.501' => 'ERROR_CC_DECLINED_CARD',
            '100.100.701' => 'ERROR_CC_DECLINED_CARD',
            '100.400.005' => 'ERROR_CC_DECLINED_CARD',
            '100.400.020' => 'ERROR_CC_DECLINED_CARD',
            '100.400.021' => 'ERROR_CC_DECLINED_CARD',
            '100.400.030' => 'ERROR_CC_DECLINED_CARD',
            '100.400.039' => 'ERROR_CC_DECLINED_CARD',
            '100.400.081' => 'ERROR_CC_DECLINED_CARD',
            '100.400.100' => 'ERROR_CC_DECLINED_CARD',
            '100.400.123' => 'ERROR_CC_DECLINED_CARD',
            '100.400.319' => 'ERROR_CC_DECLINED_CARD',
            '800.100.154' => 'ERROR_CC_DECLINED_CARD',
            '800.100.156' => 'ERROR_CC_DECLINED_CARD',
            '800.100.158' => 'ERROR_CC_DECLINED_CARD',
            '800.100.160' => 'ERROR_CC_DECLINED_CARD',
            '800.100.161' => 'ERROR_CC_DECLINED_CARD',
            '800.100.163' => 'ERROR_CC_DECLINED_CARD',
            '800.100.164' => 'ERROR_CC_DECLINED_CARD',
            '800.100.166' => 'ERROR_CC_DECLINED_CARD',
            '800.100.167' => 'ERROR_CC_DECLINED_CARD',
            '800.100.169' => 'ERROR_CC_DECLINED_CARD',
            '800.100.170' => 'ERROR_CC_DECLINED_CARD',
            '800.100.173' => 'ERROR_CC_DECLINED_CARD',
            '800.100.174' => 'ERROR_CC_DECLINED_CARD',
            '800.100.175' => 'ERROR_CC_DECLINED_CARD',
            '800.100.176' => 'ERROR_CC_DECLINED_CARD',
            '800.100.177' => 'ERROR_CC_DECLINED_CARD',
            '800.100.190' => 'ERROR_CC_DECLINED_CARD',
            '800.100.191' => 'ERROR_CC_DECLINED_CARD',
            '800.100.196' => 'ERROR_CC_DECLINED_CARD',
            '800.100.197' => 'ERROR_CC_DECLINED_CARD',
            '800.100.168' => 'ERROR_CC_DECLINED_CARD',

            '100.100.303' => 'ERROR_CC_EXPIRED',

            '800.100.153' => 'ERROR_CC_INVALIDCVV',
            '100.100.601' => 'ERROR_CC_INVALIDCVV',
            '800.100.192' => 'ERROR_CC_INVALIDCVV',

            '800.100.157' => 'ERROR_CC_EXPIRY',

            '800.100.162' => 'ERROR_CC_LIMIT_EXCEED',

            '100.400.040' => 'ERROR_CC_3DAUTH',
            '100.400.060' => 'ERROR_CC_3DAUTH',
            '100.400.080' => 'ERROR_CC_3DAUTH',
            '100.400.120' => 'ERROR_CC_3DAUTH',
            '100.400.260' => 'ERROR_CC_3DAUTH',
            '800.900.300' => 'ERROR_CC_3DAUTH',
            '800.900.301' => 'ERROR_CC_3DAUTH',
            '800.900.302' => 'ERROR_CC_3DAUTH',
            '100.380.401' => 'ERROR_CC_3DAUTH',

            '100.390.105' => 'ERROR_CC_3DERROR',
            '000.400.103' => 'ERROR_CC_3DERROR',
            '000.400.104' => 'ERROR_CC_3DERROR',
            '100.390.106' => 'ERROR_CC_3DERROR',
            '100.390.107' => 'ERROR_CC_3DERROR',
            '100.390.108' => 'ERROR_CC_3DERROR',
            '100.390.109' => 'ERROR_CC_3DERROR',
            '100.390.111' => 'ERROR_CC_3DERROR',
            '800.400.200' => 'ERROR_CC_3DERROR',
            '100.390.112' => 'ERROR_CC_3DERROR',

            '100.100.500' => 'ERROR_CC_NOBRAND',

            '800.100.155' => 'ERROR_GENERAL_LIMIT_AMOUNT',
            '000.100.203' => 'ERROR_GENERAL_LIMIT_AMOUNT',
            '100.550.310' => 'ERROR_GENERAL_LIMIT_AMOUNT',
            '100.550.311' => 'ERROR_GENERAL_LIMIT_AMOUNT',

            '800.120.101' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.100' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.102' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.103' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.200' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.201' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.202' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',
            '800.120.203' => 'ERROR_GENERAL_LIMIT_TRANSACTIONS',

            '800.100.152' => 'ERROR_CC_DECLINED_AUTH',
            '000.400.106' => 'ERROR_CC_DECLINED_AUTH',
            '000.400.105' => 'ERROR_CC_DECLINED_AUTH',
            '000.400.103' => 'ERROR_CC_DECLINED_AUTH',

            '100.380.501' => 'ERROR_GENERAL_DECLINED_RISK',

            '800.400.151' => 'ERROR_CC_ADDRESS',
            '800.400.150' => 'ERROR_CC_ADDRESS',

            '100.400.300' => 'ERROR_GENERAL_CANCEL',
            '100.396.101' => 'ERROR_GENERAL_CANCEL',
            '900.300.600' => 'ERROR_GENERAL_CANCEL',

            '800.100.501' => 'ERROR_CC_RECURRING',
            '800.100.500' => 'ERROR_CC_RECURRING',

            '800.100.178' => 'ERROR_CC_REPEATED',
            '800.300.500' => 'ERROR_CC_REPEATED',
            '800.300.501' => 'ERROR_CC_REPEATED',

            '800.700.101' => 'ERROR_GENERAL_ADDRESS',
            '800.700.201' => 'ERROR_GENERAL_ADDRESS',
            '800.700.500' => 'ERROR_GENERAL_ADDRESS',
            '800.800.102' => 'ERROR_GENERAL_ADDRESS',
            '800.800.202' => 'ERROR_GENERAL_ADDRESS',
            '800.800.302' => 'ERROR_GENERAL_ADDRESS',
            '800.900.101' => 'ERROR_GENERAL_ADDRESS',
            '800.100.198' => 'ERROR_GENERAL_ADDRESS',
            '000.100.201' => 'ERROR_GENERAL_ADDRESS',

            '100.400.121' => 'ERROR_GENERAL_BLACKLIST',
            '800.100.172' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.159' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.160' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.165' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.202' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.208' => 'ERROR_GENERAL_BLACKLIST',
            '800.200.220' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.101' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.102' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.200' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.301' => 'ERROR_GENERAL_BLACKLIST',
            '800.300.302' => 'ERROR_GENERAL_BLACKLIST',

            '000.100.200' => 'ERROR_GENERAL_GENERAL',
            '000.100.202' => 'ERROR_GENERAL_GENERAL',
            '000.100.206' => 'ERROR_GENERAL_GENERAL',
            '000.100.207' => 'ERROR_GENERAL_GENERAL',
            '000.100.208' => 'ERROR_GENERAL_GENERAL',
            '000.100.209' => 'ERROR_GENERAL_GENERAL',
            '000.100.210' => 'ERROR_GENERAL_GENERAL',
            '000.100.220' => 'ERROR_GENERAL_GENERAL',
            '000.100.221' => 'ERROR_GENERAL_GENERAL',
            '000.100.222' => 'ERROR_GENERAL_GENERAL',
            '000.100.223' => 'ERROR_GENERAL_GENERAL',
            '000.100.224' => 'ERROR_GENERAL_GENERAL',
            '000.100.225' => 'ERROR_GENERAL_GENERAL',
            '000.100.226' => 'ERROR_GENERAL_GENERAL',
            '000.100.227' => 'ERROR_GENERAL_GENERAL',
            '000.100.228' => 'ERROR_GENERAL_GENERAL',
            '000.100.229' => 'ERROR_GENERAL_GENERAL',
            '000.100.230' => 'ERROR_GENERAL_GENERAL',
            '000.100.299' => 'ERROR_GENERAL_GENERAL',
            '000.400.102' => 'ERROR_GENERAL_GENERAL',
            '000.400.200' => 'ERROR_GENERAL_GENERAL',
            '100.211.105' => 'ERROR_GENERAL_GENERAL',
            '100.211.106' => 'ERROR_GENERAL_GENERAL',
            '100.212.101' => 'ERROR_GENERAL_GENERAL',
            '100.212.102' => 'ERROR_GENERAL_GENERAL',
            '100.212.103' => 'ERROR_GENERAL_GENERAL',
            '100.250.100' => 'ERROR_GENERAL_GENERAL',
            '100.370.100' => 'ERROR_GENERAL_GENERAL',
            '100.380.100' => 'ERROR_GENERAL_GENERAL',
            '100.390.110' => 'ERROR_GENERAL_GENERAL',
            '100.390.113' => 'ERROR_GENERAL_GENERAL',
            '100.395.501' => 'ERROR_GENERAL_GENERAL',
            '100.396.102' => 'ERROR_GENERAL_GENERAL',
            '100.396.103' => 'ERROR_GENERAL_GENERAL',
            '100.396.104' => 'ERROR_GENERAL_GENERAL',
            '100.396.106' => 'ERROR_GENERAL_GENERAL',
            '100.396.201' => 'ERROR_GENERAL_GENERAL',
            '100.397.101' => 'ERROR_GENERAL_GENERAL',
            '100.397.102' => 'ERROR_GENERAL_GENERAL',
            '100.400.007' => 'ERROR_GENERAL_GENERAL',
            '100.400.041' => 'ERROR_GENERAL_GENERAL',
            '100.400.042' => 'ERROR_GENERAL_GENERAL',
            '100.400.043' => 'ERROR_GENERAL_GENERAL',
            '100.400.044' => 'ERROR_GENERAL_GENERAL',
            '100.400.045' => 'ERROR_GENERAL_GENERAL',
            '100.400.051' => 'ERROR_GENERAL_GENERAL',
            '100.400.061' => 'ERROR_GENERAL_GENERAL',
            '100.400.063' => 'ERROR_GENERAL_GENERAL',
            '100.400.064' => 'ERROR_GENERAL_GENERAL',
            '100.400.065' => 'ERROR_GENERAL_GENERAL',
            '100.400.071' => 'ERROR_GENERAL_GENERAL',
            '100.400.083' => 'ERROR_GENERAL_GENERAL',
            '100.400.084' => 'ERROR_GENERAL_GENERAL',
            '100.400.085' => 'ERROR_GENERAL_GENERAL',
            '100.400.091' => 'ERROR_GENERAL_GENERAL',
            '100.400.122' => 'ERROR_GENERAL_GENERAL',
            '100.400.130' => 'ERROR_GENERAL_GENERAL',
            '100.400.139' => 'ERROR_GENERAL_GENERAL',
            '100.400.140' => 'ERROR_GENERAL_GENERAL',
            '100.400.243' => 'ERROR_GENERAL_GENERAL',
            '100.400.301' => 'ERROR_GENERAL_GENERAL',
            '100.400.303' => 'ERROR_GENERAL_GENERAL',
            '100.400.304' => 'ERROR_GENERAL_GENERAL',
            '100.400.305' => 'ERROR_GENERAL_GENERAL',
            '100.400.306' => 'ERROR_GENERAL_GENERAL',
            '100.400.307' => 'ERROR_GENERAL_GENERAL',
            '100.400.308' => 'ERROR_GENERAL_GENERAL',
            '100.400.309' => 'ERROR_GENERAL_GENERAL',
            '100.400.310' => 'ERROR_GENERAL_GENERAL',
            '100.400.311' => 'ERROR_GENERAL_GENERAL',
            '100.400.312' => 'ERROR_GENERAL_GENERAL',
            '100.400.313' => 'ERROR_GENERAL_GENERAL',
            '100.400.314' => 'ERROR_GENERAL_GENERAL',
            '100.400.315' => 'ERROR_GENERAL_GENERAL',
            '100.400.318' => 'ERROR_GENERAL_GENERAL',
            '100.400.320' => 'ERROR_GENERAL_GENERAL',
            '100.400.321' => 'ERROR_GENERAL_GENERAL',
            '100.400.322' => 'ERROR_GENERAL_GENERAL',
            '100.400.323' => 'ERROR_GENERAL_GENERAL',
            '100.400.324' => 'ERROR_GENERAL_GENERAL',
            '100.400.325' => 'ERROR_GENERAL_GENERAL',
            '100.400.326' => 'ERROR_GENERAL_GENERAL',
            '100.400.327' => 'ERROR_GENERAL_GENERAL',
            '100.400.328' => 'ERROR_GENERAL_GENERAL',
            '100.400.500' => 'ERROR_GENERAL_GENERAL',
            '100.500.101' => 'ERROR_GENERAL_GENERAL',
            '100.500.201' => 'ERROR_GENERAL_GENERAL',
            '100.500.301' => 'ERROR_GENERAL_GENERAL',
            '100.500.302' => 'ERROR_GENERAL_GENERAL',
            '100.550.300' => 'ERROR_GENERAL_GENERAL',
            '100.550.301' => 'ERROR_GENERAL_GENERAL',
            '100.550.303' => 'ERROR_GENERAL_GENERAL',
            '100.550.312' => 'ERROR_GENERAL_GENERAL',
            '100.550.400' => 'ERROR_GENERAL_GENERAL',
            '100.550.401' => 'ERROR_GENERAL_GENERAL',
            '100.550.601' => 'ERROR_GENERAL_GENERAL',
            '100.550.603' => 'ERROR_GENERAL_GENERAL',
            '100.550.605' => 'ERROR_GENERAL_GENERAL',
            '100.600.500' => 'ERROR_GENERAL_GENERAL',
            '100.700.100' => 'ERROR_GENERAL_GENERAL',
            '100.700.101' => 'ERROR_GENERAL_GENERAL',
            '100.700.200' => 'ERROR_GENERAL_GENERAL',
            '100.700.201' => 'ERROR_GENERAL_GENERAL',
            '100.700.300' => 'ERROR_GENERAL_GENERAL',
            '100.700.400' => 'ERROR_GENERAL_GENERAL',
            '100.700.500' => 'ERROR_GENERAL_GENERAL',
            '100.700.800' => 'ERROR_GENERAL_GENERAL',
            '100.700.801' => 'ERROR_GENERAL_GENERAL',
            '100.700.802' => 'ERROR_GENERAL_GENERAL',
            '100.700.810' => 'ERROR_GENERAL_GENERAL',
            '100.800.100' => 'ERROR_GENERAL_GENERAL',
            '100.800.101' => 'ERROR_GENERAL_GENERAL',
            '100.800.102' => 'ERROR_GENERAL_GENERAL',
            '100.800.200' => 'ERROR_GENERAL_GENERAL',
            '100.800.201' => 'ERROR_GENERAL_GENERAL',
            '100.800.202' => 'ERROR_GENERAL_GENERAL',
            '100.800.300' => 'ERROR_GENERAL_GENERAL',
            '100.800.301' => 'ERROR_GENERAL_GENERAL',
            '100.800.302' => 'ERROR_GENERAL_GENERAL',
            '100.800.400' => 'ERROR_GENERAL_GENERAL',
            '100.800.401' => 'ERROR_GENERAL_GENERAL',
            '100.800.500' => 'ERROR_GENERAL_GENERAL',
            '100.800.501' => 'ERROR_GENERAL_GENERAL',
            '100.900.100' => 'ERROR_GENERAL_GENERAL',
            '100.900.101' => 'ERROR_GENERAL_GENERAL',
            '100.900.105' => 'ERROR_GENERAL_GENERAL',
            '100.900.200' => 'ERROR_GENERAL_GENERAL',
            '100.900.300' => 'ERROR_GENERAL_GENERAL',
            '100.900.301' => 'ERROR_GENERAL_GENERAL',
            '100.900.400' => 'ERROR_GENERAL_GENERAL',
            '100.900.401' => 'ERROR_GENERAL_GENERAL',
            '100.900.450' => 'ERROR_GENERAL_GENERAL',
            '100.900.500' => 'ERROR_GENERAL_GENERAL',
            '200.100.101' => 'ERROR_GENERAL_GENERAL',
            '200.100.102' => 'ERROR_GENERAL_GENERAL',
            '200.100.103' => 'ERROR_GENERAL_GENERAL',
            '200.100.150' => 'ERROR_GENERAL_GENERAL',
            '200.100.151' => 'ERROR_GENERAL_GENERAL',
            '200.100.199' => 'ERROR_GENERAL_GENERAL',
            '200.100.201' => 'ERROR_GENERAL_GENERAL',
            '200.100.300' => 'ERROR_GENERAL_GENERAL',
            '200.100.301' => 'ERROR_GENERAL_GENERAL',
            '200.100.302' => 'ERROR_GENERAL_GENERAL',
            '200.100.401' => 'ERROR_GENERAL_GENERAL',
            '200.100.402' => 'ERROR_GENERAL_GENERAL',
            '200.100.403' => 'ERROR_GENERAL_GENERAL',
            '200.100.404' => 'ERROR_GENERAL_GENERAL',
            '200.100.501' => 'ERROR_GENERAL_GENERAL',
            '200.100.502' => 'ERROR_GENERAL_GENERAL',
            '200.100.503' => 'ERROR_GENERAL_GENERAL',
            '200.100.504' => 'ERROR_GENERAL_GENERAL',
            '200.200.106' => 'ERROR_GENERAL_GENERAL',
            '200.300.403' => 'ERROR_GENERAL_GENERAL',
            '200.300.404' => 'ERROR_GENERAL_REDIRECT',
            '200.300.405' => 'ERROR_GENERAL_GENERAL',
            '200.300.406' => 'ERROR_GENERAL_GENERAL',
            '200.300.407' => 'ERROR_GENERAL_GENERAL',
            '500.100.201' => 'ERROR_GENERAL_GENERAL',
            '500.100.202' => 'ERROR_GENERAL_GENERAL',
            '500.100.203' => 'ERROR_GENERAL_GENERAL',
            '500.100.301' => 'ERROR_GENERAL_GENERAL',
            '500.100.302' => 'ERROR_GENERAL_GENERAL',
            '500.100.303' => 'ERROR_GENERAL_GENERAL',
            '500.100.304' => 'ERROR_GENERAL_GENERAL',
            '500.100.401' => 'ERROR_GENERAL_GENERAL',
            '500.100.402' => 'ERROR_GENERAL_GENERAL',
            '500.100.403' => 'ERROR_GENERAL_GENERAL',
            '500.200.101' => 'ERROR_GENERAL_GENERAL',
            '600.100.100' => 'ERROR_GENERAL_GENERAL',
            '600.200.100' => 'ERROR_GENERAL_GENERAL',
            '600.200.200' => 'ERROR_GENERAL_GENERAL',
            '600.200.201' => 'ERROR_GENERAL_GENERAL',
            '600.200.202' => 'ERROR_GENERAL_GENERAL',
            '600.200.300' => 'ERROR_GENERAL_GENERAL',
            '600.200.310' => 'ERROR_GENERAL_GENERAL',
            '600.200.400' => 'ERROR_GENERAL_GENERAL',
            '600.200.500' => 'ERROR_GENERAL_GENERAL',
            '600.200.600' => 'ERROR_GENERAL_GENERAL',
            '600.200.700' => 'ERROR_GENERAL_GENERAL',
            '600.200.800' => 'ERROR_GENERAL_GENERAL',
            '600.200.810' => 'ERROR_GENERAL_GENERAL',
            '700.100.100' => 'ERROR_GENERAL_GENERAL',
            '700.100.200' => 'ERROR_GENERAL_GENERAL',
            '700.100.300' => 'ERROR_GENERAL_GENERAL',
            '700.100.400' => 'ERROR_GENERAL_GENERAL',
            '700.100.500' => 'ERROR_GENERAL_GENERAL',
            '700.100.600' => 'ERROR_GENERAL_GENERAL',
            '700.100.700' => 'ERROR_GENERAL_GENERAL',
            '700.100.701' => 'ERROR_GENERAL_GENERAL',
            '700.100.710' => 'ERROR_GENERAL_GENERAL',
            '700.300.500' => 'ERROR_GENERAL_GENERAL',
            '700.400.000' => 'ERROR_GENERAL_GENERAL',
            '700.400.400' => 'ERROR_GENERAL_GENERAL',
            '700.400.402' => 'ERROR_GENERAL_GENERAL',
            '700.400.410' => 'ERROR_GENERAL_GENERAL',
            '700.400.420' => 'ERROR_GENERAL_GENERAL',
            '700.400.562' => 'ERROR_GENERAL_GENERAL',
            '700.400.570' => 'ERROR_GENERAL_GENERAL',
            '700.400.700' => 'ERROR_GENERAL_GENERAL',
            '700.450.001' => 'ERROR_GENERAL_GENERAL',
            '800.100.100' => 'ERROR_GENERAL_GENERAL',
            '800.100.150' => 'ERROR_GENERAL_GENERAL',
            '800.100.179' => 'ERROR_GENERAL_GENERAL',
            '800.110.100' => 'ERROR_GENERAL_GENERAL',
            '800.120.300' => 'ERROR_GENERAL_GENERAL',
            '800.120.401' => 'ERROR_GENERAL_GENERAL',
            '800.121.100' => 'ERROR_GENERAL_GENERAL',
            '800.130.100' => 'ERROR_GENERAL_GENERAL',
            '800.140.100' => 'ERROR_GENERAL_GENERAL',
            '800.140.101' => 'ERROR_GENERAL_GENERAL',
            '800.140.110' => 'ERROR_GENERAL_GENERAL',
            '800.140.111' => 'ERROR_GENERAL_GENERAL',
            '800.140.112' => 'ERROR_GENERAL_GENERAL',
            '800.140.113' => 'ERROR_GENERAL_GENERAL',
            '800.160.100' => 'ERROR_GENERAL_GENERAL',
            '800.160.110' => 'ERROR_GENERAL_GENERAL',
            '800.160.120' => 'ERROR_GENERAL_GENERAL',
            '800.160.130' => 'ERROR_GENERAL_GENERAL',
            '800.400.100' => 'ERROR_GENERAL_GENERAL',
            '800.400.101' => 'ERROR_GENERAL_GENERAL',
            '800.400.102' => 'ERROR_GENERAL_GENERAL',
            '800.400.103' => 'ERROR_GENERAL_GENERAL',
            '800.400.104' => 'ERROR_GENERAL_GENERAL',
            '800.400.105' => 'ERROR_GENERAL_GENERAL',
            '800.400.110' => 'ERROR_GENERAL_GENERAL',
            '800.400.500' => 'ERROR_GENERAL_GENERAL',
            '800.500.100' => 'ERROR_GENERAL_GENERAL',
            '800.500.110' => 'ERROR_GENERAL_GENERAL',
            '800.600.100' => 'ERROR_GENERAL_GENERAL',
            '800.700.100' => 'ERROR_GENERAL_GENERAL',
            '800.800.800' => 'ERROR_GENERAL_GENERAL',
            '800.800.801' => 'ERROR_GENERAL_GENERAL',
            '800.900.100' => 'ERROR_GENERAL_GENERAL',
            '800.900.201' => 'ERROR_GENERAL_GENERAL',
            '800.900.303' => 'ERROR_GENERAL_GENERAL',
            '800.900.401' => 'ERROR_GENERAL_GENERAL',
            '900.100.100' => 'ERROR_GENERAL_GENERAL',
            '900.100.200' => 'ERROR_GENERAL_GENERAL',
            '900.100.201' => 'ERROR_GENERAL_GENERAL',
            '900.100.202' => 'ERROR_GENERAL_GENERAL',
            '900.100.203' => 'ERROR_GENERAL_GENERAL',
            '900.100.300' => 'ERROR_GENERAL_GENERAL',
            '900.100.400' => 'ERROR_GENERAL_GENERAL',
            '900.100.500' => 'ERROR_GENERAL_GENERAL',
            '900.100.600' => 'ERROR_GENERAL_GENERAL',
            '900.200.100' => 'ERROR_GENERAL_GENERAL',
            '900.400.100' => 'ERROR_GENERAL_GENERAL',
            '999.999.999' => 'ERROR_GENERAL_GENERAL',

            '000.400.107' => 'ERROR_GENERAL_TIMEOUT',
            '100.395.502' => 'ERROR_GENERAL_TIMEOUT',

            '100.395.101' => 'ERROR_GIRO_NOSUPPORT',
            '100.395.102' => 'ERROR_GIRO_NOSUPPORT',

            '700.400.100' => 'ERROR_CAPTURE_BACKEND',
            '700.400.101' => 'ERROR_CAPTURE_BACKEND',
            '700.400.510' => 'ERROR_CAPTURE_BACKEND',

            '800.100.500' => 'ERROR_REORDER_BACKEND',
            '800.100.501' => 'ERROR_REORDER_BACKEND',

            '700.300.300' => 'ERROR_REFUND_BACKEND',
            '700.300.400' => 'ERROR_REFUND_BACKEND',
            '700.300.600' => 'ERROR_REFUND_BACKEND',
            '700.300.700' => 'ERROR_REFUND_BACKEND',
            '700.400.200' => 'ERROR_REFUND_BACKEND',
            '700.400.300' => 'ERROR_REFUND_BACKEND',
            '700.400.520' => 'ERROR_REFUND_BACKEND',
            '700.400.530' => 'ERROR_REFUND_BACKEND',
            '700.300.100' => 'ERROR_REFUND_BACKEND',

            '700.400.560' => 'ERROR_RECEIPT_BACKEND',
            '700.400.561' => 'ERROR_RECEIPT_BACKEND',

            '800.900.200' => 'ERROR_ADDRESS_PHONE'
        ];
        if ($code) {
            return array_key_exists($code, $errorMessages) ? $errorMessages[$code] : 'ERROR_UNKNOWN';
        } else {
            return 'ERROR_UNKNOWN';
        }
    }

    /**
     * Get error identifier for back office operations
     * @param  string $code
     * @return string
     */
    public function getErrorIdentifierBackend($code)
    {
        $errorMessages = [
            '700.400.100' => 'ERROR_CAPTURE_BACKEND',
            '700.400.101' => 'ERROR_CAPTURE_BACKEND',
            '700.400.510' => 'ERROR_CAPTURE_BACKEND',

            '800.100.500' => 'ERROR_REORDER_BACKEND',
            '800.100.501' => 'ERROR_REORDER_BACKEND',

            '700.300.300' => 'ERROR_REFUND_BACKEND',
            '700.300.400' => 'ERROR_REFUND_BACKEND',
            '700.300.600' => 'ERROR_REFUND_BACKEND',
            '700.300.700' => 'ERROR_REFUND_BACKEND',
            '700.400.200' => 'ERROR_REFUND_BACKEND',
            '700.400.300' => 'ERROR_REFUND_BACKEND',
            '700.400.520' => 'ERROR_REFUND_BACKEND',
            '700.400.530' => 'ERROR_REFUND_BACKEND',
            '700.300.100' => 'ERROR_REFUND_BACKEND',

            '700.400.560' => 'ERROR_RECEIPT_BACKEND',
            '700.400.561' => 'ERROR_RECEIPT_BACKEND'
        ];
        if ($code) {
            return array_key_exists($code, $errorMessages) ? $errorMessages[$code] : 'ERROR_GENERAL_PROCESSING';
        } else {
            return 'ERROR_GENERAL_PROCESSING';
        }
    }

    /**
     * Check if response code from gateway is 'in review'
     * @param  tring|boolean  $code
     * @return boolean
     */
    public function isSuccessReview($code)
    {
        $inReviews = [
            '000.400.000',
            '000.400.010',
            '000.400.020',
            '000.400.030',
            '000.400.040',
            '000.400.050',
            '000.400.060',
            '000.400.070',
            '000.400.080',
            '000.400.090'
        ];
        if (in_array($code, $inReviews)) {
            return true;
        } else {
            return false;
        }
    }
}
