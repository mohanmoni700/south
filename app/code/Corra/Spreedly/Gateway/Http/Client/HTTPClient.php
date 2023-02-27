<?php
/**
 * @author  CORRA
 */

namespace Corra\Spreedly\Gateway\Http\Client;

use Magento\Payment\Model\Method\Logger;
use Corra\Spreedly\Gateway\Config\Config;
use Magento\Framework\HTTP\ZendClient;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Command\CommandException;
use Psr\Log\LoggerInterface;

class HTTPClient implements ClientInterface
{
    private const MASK_KEY = '*';

    /**
     * @ref https://alfakher.atlassian.net/browse/OOKA-50
     * List of gateway specific fields that can be specified in supported gateway transactions.
     */
    private const GATEWAY_SPECIFIC_FIELDS = 'gateway_specific_fields';

    /**
     * @var ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var ZendClient
     */
    protected $client;

    /**
     * @var  Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Logger
     */
    protected $customLogger;

    /**
     * @var Json
     */
    protected $json;

    /**
     * HTTPClient constructor.
     * @param ZendClientFactory $httpClientFactory
     * @param LoggerInterface $logger
     * @param Json $json
     * @param Logger $customLogger
     * @param Config $config
     */
    public function __construct(
        ZendClientFactory $httpClientFactory,
        LoggerInterface $logger,
        Json $json,
        Logger $customLogger,
        Config $config
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->logger = $logger;
        $this->json = $json;
        $this->customLogger = $customLogger;
        $this->config = $config;
        $this->createClient();
    }

    /**
     * Places request to gateway.
     *
     * @ref https://alfakher.atlassian.net/browse/OOKA-50
     * @param TransferInterface $transferObject
     * @return Zend_Http_Response|array
     * @throws Exception
     * @throws CommandException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = [];
        $this->client->setUri($transferObject->getUri());
        $this->client->setHeaders($transferObject->getHeaders());

        if (!empty($transferObject->getBody()) && $transferObject->getBody()) {
            $data = $transferObject->getBody();

            /** Getting gateway_specific_fields for active store **/
            $gatewaySpecificFields = ($this->config->getGatewaySpecificFieldsJsonData()) ?
                $this->config->getGatewaySpecificFieldsJsonData() : false;

            if ($gatewaySpecificFields) {
                /** Merging "gateway_specific_fields" on the original request **/
                $data['transaction'][self::GATEWAY_SPECIFIC_FIELDS] = $gatewaySpecificFields;
            }

            $dataString = $this->json->serialize($data);
            $this->client->setRawData($dataString, 'application/json');
            $logQuery = $this->maskLogData($data);
            $log = [
                'request' => $logQuery,
                'client' => static::class
            ];
        }
        try {
            $response = $this->client->request($transferObject->getMethod());
            $rawResponse = $response->getRawBody();
            $response = $this->json->unserialize($rawResponse);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new ClientException($e->getMessage());
        } finally {
            $log['url'] = $transferObject->getUri();
            $log['response'] = $response;
            $this->customLogger->debug($log);
        }
        if (isset($response['transaction']['succeeded']) &&
            $response['transaction']['succeeded'] == false &&
            isset($response['transaction']['message']) &&
            !empty($response['transaction']['message'])
        ) {
            throw new CommandException(
                __($response['transaction']['message'])
            );
        }
        return $response;
    }

    /**
     * Create ZendClient
     */
    private function createClient()
    {
        /** @var ZendClient $client */
        $this->client = $this->httpClientFactory->create();
    }

    /**
     * Mask Logger Data
     *
     * @param array $data
     * @return string
     */
    protected function maskLogData($data)
    {
        if (!empty($data['transaction']['credit_card']['verification_value'])) {
            $data['transaction']['credit_card']['verification_value'] = "xxx";
        }
        if (!empty($data['transaction']['credit_card']['number'])) {
            $cardNumber = $data['transaction']['credit_card']['number'];
            $data['transaction']['credit_card']['number'] =  str_pad(
                substr($cardNumber, strlen($cardNumber)-4, 4),
                strlen($cardNumber),
                self::MASK_KEY,
                STR_PAD_LEFT
            );
        }
        return $this->json->serialize($data);
    }
}
