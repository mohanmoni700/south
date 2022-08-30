<?php

namespace Alfakher\Seamlesschex\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class SeamlesschexPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment method code
     *
     * @var string $_code
     */
    protected $_code = 'seamlesschex';

    /**
     * Initilization required
     *
     * @var boolean $_isInitializeNeeded
     */
    protected $_isInitializeNeeded = true;
    
    /**
     * Payment infor block
     *
     * @var \Alfakher\Seamlesschex\Block\Info\Instructions $_infoBlockType
     */
    protected $_infoBlockType = \Alfakher\Seamlesschex\Block\Info\Instructions::class;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Alfakher\Seamlesschex\Helper\Data $seamlesschexHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param DirectoryHelper $directory
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Alfakher\Seamlesschex\Helper\Data $seamlesschexHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
        $this->_paymentData = $paymentData;
        $this->_scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->directory = $directory ?: ObjectManager::getInstance()->get(DirectoryHelper::class);
        $this->initializeData($data);

        $this->_seamlesschexHelper = $seamlesschexHelper;
        $this->_messageManager = $messageManager;
        $this->_curl = $curl;
        $this->_quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize
     *
     * @param string $paymentAction
     * @param object $stateObject
     */
    public function initialize(
        $paymentAction,
        $stateObject
    ) {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $amount = $order->getTotalDue();
        $this->$paymentAction($payment, $amount);
    }

    /**
     * Authorize
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     */
    public function authorize(
        \Magento\Payment\Model\InfoInterface $payment,
        $amount
    ) {
        try {
            $order = $payment->getOrder();
            $quoteid = $order->getQuoteId();

            if ($quoteid) {
                $connectionResult = $this->_seamlesschexHelper
                ->testConnection($order->getStore()->getWebsite()->getId());

                if ($connectionResult['status']) {
                    $currQuote = $this->quoteRepository->get($quoteid);

                    $requestPayload = $this->prepareRequest($order, $currQuote);
                    $requestPayload['amount'] = $amount;

                    $response = $this->createCheck($requestPayload, $order);
                    $apiResponse = json_decode($response['response'], 1);

                    /* add logs; Start */
                    $this->_seamlesschexHelper->addLog(
                        \Alfakher\Seamlesschex\Helper\Data::TYPE_CREATE,
                        $order->getIncrementId(),
                        json_encode($requestPayload),
                        $response['response'],
                        $response['http_status']
                    );
                    /* add logs; End */

                    if ($response['http_status'] == 200) {
                        if ($apiResponse['check']['status'] == 'void') {
                            throw new \Magento\Framework\Validator\Exception(
                                __("Your ACH payment request has been decline,".
                                    " please try with another ACH details or choose another payment method.")
                            );
                        }
                        $payment->setTransactionId($apiResponse['check']['check_id'])
                        ->setIsTransactionClosed(0)
                        ->setAdditionalInformation(array_replace_recursive(
                            $payment->getAdditionalInformation(),
                            $apiResponse
                        ));
                    } elseif ($response['http_status'] == 402 || $response['http_status'] == 400) {
                        throw new \Magento\Framework\Validator\Exception(__($apiResponse['message']));
                    } else {
                        throw new \Magento\Framework\Validator\Exception(
                            __("Unable to place the order, there is some issue at the gateway side.")
                        );
                    }
                    
                } else {
                    throw new \Magento\Framework\Validator\Exception(
                        __("Unable to place the order, there is some issue at the gateway side.")
                    );
                }
                
            } else {
                throw new \Magento\Framework\Validator\Exception(
                    __('Unable to find any active cart')
                );
            }

            return $this;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }
        return $this;
    }

    /**
     * Prepare Request
     *
     * @param object $order
     * @param object $quote
     */
    protected function prepareRequest(
        $order,
        $quote
    ) {
        return [
            'memo' => "order #".$order->getIncrementId(),
            'name' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
            'bank_routing' => $quote->getPayment()->getAchRoutingNumber(),
            'bank_account' => $quote->getPayment()->getAchAccountNumber(),
            'phone' => $order->getBillingAddress()->getTelephone(),
            'number' => $quote->getPayment()->getAchCheckNumber()
        ];
        /**
        * 400 : validation error
        * 402 : duplicate error
        */
    }

    /**
     * Create check
     *
     * @param array $payload
     * @param object $order
     */
    private function createCheck(
        $payload,
        $order
    ) {
        $jsonPayload = json_encode($payload);
        $config = $this->_seamlesschexHelper->getConfigData($order->getStore()->getWebsite()->getId());
        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->addHeader("Authorization", "Bearer ".$config['secret_key']);
        $this->_curl->post($config['endpoint']."check/create", $jsonPayload);

        $responseStatus = $this->_curl->getStatus();
        $response = $this->_curl->getBody();

        return ['http_status' => $responseStatus, 'response' => $response];
    }

    /**
     * Assign data
     *
     * @param \Magento\Framework\DataObject $data
     */
    public function assignData(
        \Magento\Framework\DataObject $data
    ) {
        /**
        * payment_method_assign_data_seamlesschex
        * payment_method_assign_data
        */
        parent::assignData($data);
        return $this;
    }
}
