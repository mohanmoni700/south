<?php

namespace Infibeam\Ccavenue\Model;

use Magento\Sales\Api\Data\TransactionInterface;

class Ccavenue extends \Magento\Payment\Model\Method\AbstractMethod {

    const PAYMENT_CCA_CODE = 'ccavenue';

    protected $_code = self::PAYMENT_CCA_CODE;

    /**
     *
     * @var \Magento\Framework\UrlInterface 
     */
    protected $_urlBuilder;

    /**
     *
     * CCAvenue logger
     */
    protected $_logger;
    
    private $checkoutSession;
    private $countryFactory;

    /**
     * 
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Infibeam\Ccavenue\Helper\Ccavenue $helper,
        \Infibeam\Ccavenue\Logger\Logger $ccavLogger,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\Country $countryFactory

    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );
        $this->helper = $helper;
        $this->_logger = $ccavLogger;
        $this->orderSender = $orderSender;
        $this->httpClientFactory = $httpClientFactory;
        $this->checkoutSession = $checkoutSession;
        $this->countryFactory = $countryFactory;


    }

    public function canUseForCurrency($currencyCode) {
        $allowedCurrencies = $this->getConfigData('allowed_currencies');
        $allowedCurrencies = explode(",", $allowedCurrencies);
        if (!in_array($currencyCode, $allowedCurrencies)) {
            return false;
        }
        return true;
    }

    public function getRedirectUrl() {
        return $this->helper->getUrl($this->getConfigData('redirect_url'));
    }

    public function getReturnUrl() {
        return $this->helper->getUrl($this->getConfigData('return_url'));
    }

    public function getCancelUrl() {
        return $this->helper->getUrl($this->getConfigData('cancel_url'));
    }

    /**
     * Return url according to environment
     * @return string
     */
    public function getCgiUrl() {
        $env = $this->getConfigData('environment');
        if ($env === 'production') {
            return $this->getConfigData('production_url');
        }
        return $this->getConfigData('sandbox_url');
    }

    public function buildCheckoutRequest() {
        $order = $this->checkoutSession->getLastRealOrder();
        $get_data = array();

        $get_data['merchant_id'] = $this->getConfigData('merchant_id');

        $billing_address = $order->getBillingAddress();
        if (!empty($billing_address)) {
            $get_data['billing_name']       =   $billing_address->getFirstname() .' '. $billing_address->getLastname();
            $get_data['billing_address']    =   $billing_address->getStreetLine(1);
            $get_data['billing_city']       =   $billing_address->getCity();
            $get_data['billing_state']      =   $billing_address->getRegion();
            $get_data['billing_zip']        =   $billing_address->getPostcode();
            $get_data['billing_country']    =   $this->countryFactory->loadByCode($billing_address->getCountryId())->getName();
            $get_data['billing_tel']        =   $billing_address->getTelephone();
            $get_data['billing_email']      =   $order->getCustomerEmail();
        }

        $shipping_address = $order->getShippingAddress();
        if (!empty($shipping_address)) {
            $get_data['delivery_name']     = $shipping_address->getFirstname() .' '. $shipping_address->getLastname();
            $get_data['delivery_address']  = $shipping_address->getStreetLine(1);
            $get_data['delivery_city']     = $shipping_address->getCity();
            $get_data['delivery_state']    = $shipping_address->getRegion();
            $get_data['delivery_zip']      = $shipping_address->getPostcode();
            $get_data['delivery_country']  = $this->countryFactory->loadByCode($shipping_address->getCountryId())->getName();
            $get_data['delivery_tel']      = $shipping_address->getTelephone();
        }else{
            $get_data['delivery_name']     = $billing_address->getFirstname() .' '. $billing_address->getLastname();
            $get_data['delivery_address']  = $billing_address->getStreetLine(1);
            $get_data['delivery_city']     = $billing_address->getCity();
            $get_data['delivery_state']    = $billing_address->getRegion();
            $get_data['delivery_zip']      = $billing_address->getPostcode();
            $get_data['delivery_country']  = $this->countryFactory->loadByCode($billing_address->getCountryId())->getName();
            $get_data['delivery_tel']      = $billing_address->getTelephone();
        }

        $get_data['order_id']           =   $order->getIncrementId();
        $get_data['amount']             =   round($order->getGrandTotal(), 2);
        $get_data['currency']           =   $order->getOrderCurrencyCode();
        $get_data['redirect_url']       =   $this->getReturnUrl();
        $get_data['cancel_url']         =   $this->getCancelUrl();
        $get_data['language']           =   'EN';
        $get_data['tid']                =   time();
        $get_data['merchant_param1']    =   $order->getIncrementId();
        $get_data['merchant_param2']    =   $order->getId();
        
        if ($this->getConfigData('integration_type') == 'iframe')
            $get_data['integration_type'] = "iframe_normal";

        $this->_logger->info('Order Id : ' . $get_data['order_id'] . ' | Amount : '. $get_data['amount']);

        $merchant_data = http_build_query($get_data);

        $encryption_key = $this->getConfigData('encryption_key');
        $params["integration_type"] = $this->getConfigData('integration_type');
        $params["access_code"] = $this->getConfigData('access_code');
        $params["encRequest"] = $this->encrypt($merchant_data, $encryption_key);

        $this->_logger->info('Encrypted Request : ' . $params["encRequest"]);

        return $params;
    }

    public function encrypt($plainText, $key) {
        $key = hex2bin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }

    public function decrypt($encryptedText, $key) {
        $key = hex2bin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = hex2bin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }

    //decrypt response
    public function decryptResponse($returnParams) {
        $response = array();

        $this->_logger->info('Encrypted Response : ' . $returnParams['encResp']);

        if (!isset($returnParams['encResp'])) {
            return $response;
        }

        $encryption_key = $this->getConfigData('encryption_key');
        $rcvdString = $this->decrypt($returnParams['encResp'], $encryption_key);
        $decryptValues = explode('&', $rcvdString);

        for ($i = 0; $i < count($decryptValues); $i++) {
            $information = explode('=', $decryptValues[$i]);
            if (count($information) == 2) {
                $response[$information[0]] = $information[1];
            }
        }

        return $response;
    }

    public function postProcessing(\Magento\Sales\Model\Order $order,
        \Magento\Framework\DataObject $payment, $response) {

        $isCustomerNotified = false;
        $orderComment = 'Txn Id: ' . $response['tracking_id'] . ' | Txn Status: ' . $response['order_status'] . ' | Payment Mode: ' . $response['payment_mode'] . ' | Amount: ' . $response['amount'];
        $payment->setTransactionId($response['tracking_id']);
        if($response['order_status'] != 'Success'){
            $payment->setTransactionAdditionalInfo('status_message', $response['failure_message']);
            $payment->setTransactionAdditionalInfo('payment_mode', $response['payment_mode']);
        } else {
            $isCustomerNotified = true;
            $payment->setTransactionAdditionalInfo('status_message', $response['status_message']);
            $payment->setTransactionAdditionalInfo('payment_mode', $response['payment_mode']);
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
            $order->setStatus($this->getConfigData('payment_success_order_status'));
            $order->setCustomerNoteNotify(true);
        }

        $order->addStatusHistoryComment($orderComment)
                ->setIsCustomerNotified($isCustomerNotified);
        $order->save();

        $this->_logger->info($response['order_id'] . ' | ' . $orderComment);

        $payment->setIsTransactionClosed(0);
        $payment->place();

        if($response['order_status'] == 'Success'){
            $this->sendOrderMail($order);
            $this->_logger->info('Order Confirmation Email Sent.');
        }

    }

    public function sendOrderMail($order) {
        $this->orderSender->send($order, false, true);
    }

}
