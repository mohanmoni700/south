<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Controller\Customer;

use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;
use Alfakher\SlopePayment\Logger\Logger;
use Alfakher\SlopePayment\Model\Gateway\Request as GatewayRequest;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Prequalify extends Action
{
    public const CREATE_CUSTOMER = '/customers';
    public const GET_CUSTOMER = '/customers/id/';
    public const GET_CUSTOMER_INTENT = '/customers/id/intent';

    /**
     * JsonFactory
     *
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var SlopeConfigHelper
     */
    protected $slopeConfig;

    /**
     * @var GatewayRequest
     */
    protected $gatewayRequest;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Class constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CustomerSession $customerSession
     * @param Json $json
     * @param SlopeConfigHelper $slopeConfig
     * @param GatewayRequest $gatewayRequest
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession,
        Json $json,
        SlopeConfigHelper $slopeConfig,
        GatewayRequest $gatewayRequest,
        Logger $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->json = $json;
        $this->config = $slopeConfig;
        $this->gatewayRequest = $gatewayRequest;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Initiate checkout flow
     *
     * @return JsonFactory
     * @throws Exception
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $messages = ['Some error occured, Please try again later'];
            $result->setData(['success' => false, 'secret' => null, 'messages' => $messages]);

            $mgtCustomer = $this->getMgtCustomerForSlope();
            $mgtCustId = $this->customerSession->getId();
            $slopeCustomer = $this->findSlopeCustomer($mgtCustId);

            $statusCode = isset($slopeCustomer['statusCode']) ? $slopeCustomer['statusCode'] : null;
            if (isset($slopeCustomer) && $statusCode === 404) {
                $slopeCustomer = $this->createNewSlopeCustomer($mgtCustomer);
                if (isset($slopeCustomer['statusCode']) && $slopeCustomer['statusCode'] !== '') {
                    $messages = $slopeCustomer['messages'][0];
                    return $result->setData(['success' => false, 'secret' => null, 'messages' => $messages]);
                }
            }

            if (isset($slopeCustomer) && isset($slopeCustomer['id']) && $slopeCustomer['id'] != '') {
                $slopeCustomerId = $slopeCustomer['id'];
                $slopePopup = $this->getSlopeCustomerIntent($slopeCustomerId);
            }

            if (isset($slopePopup['secret']) && $slopePopup['secret'] != '') {
                $result->setData(['success' => true, 'secret' => $slopePopup['secret'], 'messages' => '']);
            }

        } catch (\Exception $e) {
            if ($this->config->isDebugEnabled()) {
                $this->logger->info('Pre-Qualification Error:' . $e->getMessage());
            }
            return $result->setData(['success' => false, 'secret' => null, 'messages' => $messages]);
        }

        return $result;
    }

    /**
     * Create new slope customer
     *
     * @param array $customer
     * @return array
     */
    public function createNewSlopeCustomer($customer)
    {
        $apiEndpointUrl = $this->config->getEndpointUrl();

        $url = $apiEndpointUrl . self::CREATE_CUSTOMER;
        $response = $this->gatewayRequest->post($url, $customer);
        $response = $this->json->unserialize($response);
        return $response;
    }

    /**
     * Find slope customer by externalId
     *
     * @param int $externalId
     * @return array
     */
    public function findSlopeCustomer($externalId)
    {
        $apiEndpointUrl = $this->config->getEndpointUrl();
        $url = $apiEndpointUrl . self::GET_CUSTOMER;
        $url = str_replace("id", $externalId, $url);
        $response = $this->gatewayRequest->get($url);
        $response = $this->json->unserialize($response);
        return $response;
    }

    /**
     * Get slope customer intent secret by customer id
     *
     * @param int $slopeCustomerId
     * @return array
     */
    public function getSlopeCustomerIntent($slopeCustomerId)
    {
        $apiEndpointUrl = $this->config->getEndpointUrl();

        $url = $apiEndpointUrl . self::GET_CUSTOMER_INTENT;
        $url = str_replace("id", $slopeCustomerId, $url);
        $response = $this->gatewayRequest->post($url);
        $response = $this->json->unserialize($response);
        return $response;
    }

    /**
     * Prepare customer data for slope api
     *
     * @return json
     */
    public function getMgtCustomerForSlope()
    {
        $customerData = [];

        $customer = $this->customerSession->getCustomer();
        $address = $customer->getDefaultBillingAddress();
        $addressPhone = $address->getTelephone();

        $company = $this->config->getCustomerCompany($customer->getId());

        $addressData =
            [
            "line1" => $address->getStreetLine(1),
            "city" => $address->getCity(),
            "state" => $address->getRegionCode(),
            "postalCode" => $address->getPostcode(),
            "country" => $address->getCountry(),
        ];

        $customerData['email'] = $customer->getEmail();
        $customerData['phone'] = $this->config->getSlopeFormattedPhone($addressPhone);
        $customerData['businessName'] = $company->getCompanyName() ?: 'NA';
        $customerData['address'] = $addressData;
        $customerData['externalId'] = $customer->getId();

        return $this->json->serialize($customerData);
    }
}
