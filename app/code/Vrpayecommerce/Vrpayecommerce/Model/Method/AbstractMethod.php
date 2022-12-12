<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_abstract';
    protected $_isInitializeNeeded = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $brand = '';
    protected $accountType = '';
    protected $methodTitle = '';
    protected $adminMethodTitle = '';
    protected $paymentType = 'DB';
    protected $testMode = 'EXTERNAL';
    protected $paymentCode = '';
    protected $paymentGroup = '';
    protected $isRecurringPayment = false;
    protected $logo = '';
    protected $logoDe = '';
    protected $isLogoDe = false;
    protected $isServerToServer = false;
    protected $isCreatedOrderBeforePayment = true;

    const SHOP_SYSTEM = 'Magento';
    const CLIENT = 'CardProcess';
    const SYNCHRONOUS = 'sync';
    const ASYNCHRONOUS = 'async';

    const STATUS_PA = 'payment_pa';
    const STATUS_DB = 'payment_accepted';
    const STATUS_IR = 'payment_inreview';

    /**
     *
     * @param  string $paymentAction
     * @param  object $stateObject
     * @return object
     */
    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {
            case self::ACTION_ORDER:
                $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $stateObject->setStatus($this->getConfigData('order_status'));
                $stateObject->setIsNotified(false);
                break;
            default:
                break;
        }
    }

    /**
     * get an order
     * @return object
     */
    public function getOrder()
    {
        $infoInstance = $this->getInfoInstance();

        return $infoInstance->getOrder();
    }

    /**
     * get a title
     * @return string
     */
    public function getTitle()
    {
        if ($this->isAdminLoggedIn() && $this->isRecurringPayment()) {
            return __($this->adminMethodTitle);
        }

        return __($this->methodTitle);
    }

    /**
     * get a logo
     * @return string
     */
    public function getLogo()
    {
        $lang = $this->getLangCode();
        if ($this->isLogoDe && $lang == 'de') {
            return $this->logoDe;
        } else {
            return $this->logo;
        }
    }

    /**
     * get an order place redirect URL
     * @return boolean
     */
    public function getOrderPlaceRedirectUrl()
    {
        return true;
    }

    /**
     * get the tax configurations
     * @param $field
     * @param $storeId
     * @return mixed
     */
    public function getTaxConfig($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'tax/calculation/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * get the general configurations
     * @param  string $field
     * @param  string $storeId
     * @return void
     */
    public function getGeneralConfig($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/vrpayecommerce_general/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * get a server mode
     * @return string
     */
    public function getServerMode()
    {
        return $this->getConfigData('server_mode');
    }

    /**
     * get a channel id
     * @return string
     */
    public function getChannelId()
    {
        return $this->getConfigData('channel_id');
    }

    /**
     * get a register amount
     * @return int
     */
    public function getRegisterAmount()
    {
        return $this->getConfigData('register_amount');
    }

    /**
     * check activation multichannel
     * @return boolean
     */
    public function isMultiChannel()
    {
        return $this->getConfigData('multichannel');
    }

    /**
     * get credentials
     * @return array
     */
    public function getCredentials()
    {
        $credentials = [
            'serverMode' => $this->getServerMode(),
            'testMode' => $this->getTestMode(),
            'channelId' => $this->getChannelId(),
            'bearerToken' => $this->getGeneralConfig('bearer'),
            'login' => $this->getGeneralConfig('login'),
            'password' => $this->getGeneralConfig('password')
        ];
        if(!empty($credentials['bearerToken']))
        {
            unset($credentials['login']);
            unset($credentials['password']);
        }

        if ($this->isMultiChannel()) {
            $credentials['channelIdMoto'] = $this->getConfigData('channel_id_moto');
        }

        $credentials = array_merge($credentials, $this->getProxyParameters());

        return $credentials;
    }

    /**
     * get proxy parameters
     * @return array
     */
    public function getProxyParameters()
    {
        $proxyParameters = [];

        $proxyParameters['proxy']['behind'] = $this->getConfigData('behind_proxy');
        $proxyParameters['proxy']['url'] = $this->getConfigData('proxy_url');
        $proxyParameters['proxy']['port'] = $this->getConfigData('proxy_port');

        return $proxyParameters;
    }

    /**
     * check activation recurring
     * @return boolean
     */
    public function isRecurring()
    {
        $recurring = $this->getGeneralConfig('recurring');
        if ($recurring == 1) {
            return true;
        }

        return false;
    }

    /**
     * get the paydirekt payment is partial
     * @return string
     */
    public function paydirektPaymentIsPartial()
    {
        $partial = $this->getGeneralConfig('is_partial');
        if ($partial == 1) {
            return 'true';
        }

        return 'false';
    }

    /**
     * get a test mode
     * @return string|boolean
     */
    public function getTestMode()
    {
        if ($this->getServerMode() == "LIVE") {
            return false;
        }
        return $this->testMode;
    }

    /**
     * get a payment type selection
     * @return string
     */
    public function getPaymentTypeSelection()
    {
        return $this->getConfigData('transaction_mode');
    }

    /**
     * get cards selection
     * @return string
     */
    public function getCardSelection()
    {
        return $this->getConfigData('card_selection');
    }

    /**
     * get a payment type
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * get an account type
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * get a brand
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * get a payment code
     * @return string
     */
    public function getPaymentCode()
    {
        return $this->paymentCode;
    }

    /**
     * get a payment group
     * @return string
     */
    public function getPaymentGroup()
    {
        return $this->paymentGroup;
    }

    /**
     * check recurring of a payment
     * @return boolean
     */
    public function isRecurringPayment()
    {
        return $this->isRecurringPayment;
    }

    /**
     * check if a payment is the server to server payment
     * @return boolean
     */
    public function isServerToServer()
    {
        return $this->isServerToServer;
    }

    /**
     * check if an order is created before the payment
     * @return boolean
     */
    public function isCreatedOrderBeforePayment()
    {
        return $this->isCreatedOrderBeforePayment;
    }

    /**
     * get a widget style
     * @return string
     */
    public function getWidgetStyle()
    {
        return $this->getGeneralConfig('widget_style');
    }

    /**
     *
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  int                               $amount
     * @return object
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * get a language code
     * @return string
     */
    public function getLangCode()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $localeResolver = $objectManager->get('Magento\Framework\Locale\ResolverInterface');
        $locale = $localeResolver->getLocale();

        switch ($locale) {
            case 'de_DE':
            case 'de_AT':
            case 'de_CH':
                $langCode = 'de';
                break;
            case 'fr_FR':
            case 'fr_CA':
                $langCode = 'fr';
                break;
            case 'nl_NL':
                $langCode = 'nl';
                break;
            default:
                $langCode = 'en';
                break;
        }
        return $langCode;
    }

    /**
     * check if shipping address same as billing address
     * @return boolean
     */
    public function isShippingAddressSameAsBilling()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');

        $billingAddress = $checkoutSession->getQuote()->getBillingAddress();
        $shippingAddress = $checkoutSession->getQuote()->getShippingAddress();

        $billingData = $this->serializeAddress($billingAddress);
        $shippingData = $this->serializeAddress($shippingAddress);

        if (strcmp($shippingData, $billingData) != 0) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param  object $address
     * @return array
     */
    protected function serializeAddress($address)
    {
        return serialize(
            [
                 'firstName' => $address->getFirstname(),
                 'lastName'  => $address->getLastname(),
                 'street'    => $address->getStreet(),
                 'city'      => $address->getCity(),
                 'postCode'  => $address->getPostcode(),
                 'region'    => $address->getRegion(),
                 'countryId' => $address->getCountryId(),
                 'telephone' => $address->getTelephone()
            ]
        );
    }

    /**
     * create a customer session
     * @return object
     */
    protected function createCustomerSessionObject()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('Magento\Customer\Model\Session');
    }

    /**
     * check if admin already login
     * @return boolean
     */
    public function isAdminLoggedIn()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $adminSession = $objectManager->get('\Magento\Backend\Model\Auth\Session');

        return $adminSession->isLoggedIn();
    }

    public function getCustomerInformation()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->create('\Vrpayecommerce\Vrpayecommerce\Model\Customer\Customer');
    }

    /**
     * return error for easyCredit DoB error
     * @return string
     */
    public function isDateOfBirthValid()
    {
        $customerInformation = $this->getCustomerInformation();
        $customerDoB = $customerInformation->getDob();
        $errorDoB = 'ERROR_EASYCREDIT_PARAMETER_DOB';

        if (!empty($customerDoB)) {
            $customerDoB = explode("-", $customerDoB);
            $year = (int)$customerDoB[0];
            $month = (int)$customerDoB[1];
            $day = (int)$customerDoB[2];

            if ($year < 1900) {
                return $errorDoB;
            }

            if ($month < 1 || $month > 12) {
                return $errorDoB;
            }

            if ($day < 1 || $day > 31) {
                return $errorDoB;
            }

            $valid = checkdate($month, $day, $year);

            if ($valid) {
                return '';
            }
        }
        return $errorDoB;
    }

    /**
     * is date of birth lower than today
     *
     * @return string
     */
    public function isDateOfBirthLowerThanToday()
    {
        $customerInformation = $this->getCustomerInformation();
        $customerDoB = $customerInformation->getDob();
        $dob = strtotime($customerDoB);

        $errorDoB = '';
        $current_date = date("Y-m-d");

        if ($dob > strtotime($current_date)) {
            $errorDoB = 'ERROR_EASYCREDIT_FUTURE_DOB';
        }

        return $errorDoB;
    }

    /**
     * return error for easyCredit Amount error
     * @return string
     */
    public function easyCreditGetAmountStatusError()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');
        $quote = $checkoutSession->getQuote();
        $grandTotal = $quote->getGrandTotal();
        $currencyCode = $quote->getQuoteCurrencyCode();

        $errorAmount = '';

        if ($grandTotal < 200 || $grandTotal > 10000 || $currencyCode != "EUR") {
            $errorAmount = 'ERROR_MESSAGE_EASYCREDIT_AMOUNT_NOTALLOWED';
            //if less than 200 or more than 3000 or wrong currencycode
        }
        return $errorAmount;
    }

    /**
     * return error for easyCredit Gender error
     * @return string
     */
    public function easyCreditGetGenderStatusError()
    {
        $customerInformation = $this->getCustomerInformation();
        $customerGender = $customerInformation->getGender();

        $errorGender = '';

        if ($customerGender == false) {
            $errorGender = 'ERROR_MESSAGE_EASYCREDIT_PARAMETER_GENDER';
            //if gender is empty
        }

        return $errorGender;
    }

    /**
     * capture a payment
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  int                               $amount
     * @return object
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helperPayment = $objectManager->create('Vrpayecommerce\Vrpayecommerce\Helper\Payment');

        $paymentType = 'CP';
        $currency = $payment->getAdditionalInformation('CURRENCY');
        $referenceId = $payment->getAdditionalInformation('REFERENCE_ID');

        if ($payment->getAdditionalInformation('PAYMENT_TYPE') == 'PA') {
            $captureParameters =  $this->getCredentials();
            if ($this->isMultiChannel()) {
                $captureParameters['channelId'] = $captureParameters['channelIdMoto'];
            }
            $captureParameters['amount'] = $amount;
            $captureParameters['currency'] = $currency;
            $captureParameters['paymentType'] = $paymentType;

            $resultJson = $helperPayment->backOfficeOperation($referenceId, $captureParameters);

            if ($resultJson['isValid']) {
                $returnCode = $resultJson['response']['result']['code'];
                $returnMessage = $helperPayment->getErrorIdentifierBackend($returnCode);
                $transactionResult = $helperPayment->getTransactionResult($returnCode);

                if ($transactionResult == 'ACK') {
                    $payment->setAdditionalInformation('PAYMENT_TYPE', $paymentType);
                    $payment->setStatus('APPROVED')
                            ->setTransactionId($resultJson['response']['id'])
                            ->setIsTransactionClosed(1)->save();
                } elseif ($transactionResult == 'NOK') {
                    throw new \Magento\Framework\Exception\LocalizedException(__($returnMessage));
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('ERROR_GENERAL_PROCESSING'));
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__($resultJson['response']));
            }
        } else {
            $payment->setStatus('APPROVED')
                    ->setTransactionId($payment->getAdditionalInformation('REFERENCE_ID'))
                    ->setIsTransactionClosed(1)->save();
        }
        return $this;
    }

    /**
     * refund a payment
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  int                               $amount
     * @return object
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helperPayment = $objectManager->create('Vrpayecommerce\Vrpayecommerce\Helper\Payment');

        $paymentType = 'RF';
        $currency = $payment->getAdditionalInformation('CURRENCY');
        $referenceId = $payment->getAdditionalInformation('REFERENCE_ID');

        $refundParameters = $this->getCredentials();
        if ($this->isMultiChannel()) {
            $refundParameters['channelId'] = $refundParameters['channelIdMoto'];
        }
        $refundParameters['amount'] = $amount;
        $refundParameters['currency'] = $currency;
        $refundParameters['paymentType'] = $paymentType;

        $resultJson = $helperPayment->backOfficeOperation($referenceId, $refundParameters);

        if ($resultJson['isValid']) {
            $returnCode = $resultJson['response']['result']['code'];
            $returnMessage = $helperPayment->getErrorIdentifierBackend($returnCode);
            $transactionResult = $helperPayment->getTransactionResult($returnCode);

            if ($transactionResult == 'ACK') {
                $payment->setAdditionalInformation('PAYMENT_TYPE', $paymentType);
                $payment->setStatus('APPROVED')
                        ->setTransactionId($resultJson['response']['id'])
                        ->setIsTransactionClosed(1)->save();
            } elseif ($transactionResult == 'NOK') {
                throw new \Magento\Framework\Exception\LocalizedException(__($returnMessage));
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('ERROR_GENERAL_PROCESSING'));
            }
            return $this;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__($resultJson['response']));
        }
    }

    /**
     * get the shop version
     * @return string
     */
    public function getShopVersion()
    {
        if (defined('\Magento\Framework\AppInterface::VERSION')) {
            return \Magento\Framework\AppInterface::VERSION;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetaData = $objectManager->create('\Magento\Framework\App\ProductMetadataInterface');

        return $productMetaData->getVersion();
    }

    /**
     * get the plugin version
     * @return object
     */
    public function getPluginVersion()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $moduleList = $objectManager->create('Magento\Framework\Module\ModuleListInterface');

        return $moduleList->getOne('Vrpayecommerce_Vrpayecommerce')['setup_version'];
    }

    /**
     * get a server address
     * @return object
     */
    public function getServerAddress()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $serverAddress = $objectManager->create('Magento\Framework\HTTP\PhpEnvironment\ServerAddress');

        return $serverAddress->getServerAddress();
    }

    /**
     * get a version data
     * @return array
     */
    public function getVersionData()
    {
        $versionData = [];

        $versionData['transaction_mode'] = $this->getServerMode();
        $versionData['ip_address'] = $this->getServerAddress();
        $versionData['shop_version'] = $this->getShopVersion();
        $versionData['plugin_version'] = $this->getPluginVersion();
        $versionData['client'] = self::CLIENT;
        $versionData['merchant_id'] = $this->getGeneralConfig('merchant_no');
        $versionData['shop_system'] = self::SHOP_SYSTEM;
        $versionData['shop_url'] = $this->getGeneralConfig('shop_url');

        return $versionData;
    }

    /**
     * get version tracking data for getCheckoutResult
     *
     * @param $parameters
     * @return array
     */
    public function getTrackingDataForCheckoutResult($parameters)
    {
        $versionData = $this->getVersionData();
        $parameters = array_merge_recursive(
            $parameters,
            [
                'customParameters' => [
                    'PLUGIN_accountId'          => $versionData['merchant_id'],
                    'PLUGIN_shopUrl'            => $versionData['shop_url'],
                    'PLUGIN_shopSystem'         => $versionData['shop_system'],
                    'PLUGIN_shopSystemVersion'  => $versionData['shop_version'],
                    'PLUGIN_version'            => $versionData['plugin_version'],
                    'PLUGIN_outletLocation'     => $this->getGeneralConfig('merchant_location'),
                    'PLUGIN_mode'               => $versionData['transaction_mode'],
                ]
            ]
        );
        return $parameters;
    }
}
