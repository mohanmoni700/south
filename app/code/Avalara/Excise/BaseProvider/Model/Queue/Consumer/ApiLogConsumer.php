<?php
/*
 * Avalara_BaseProvider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Avalara\Excise\BaseProvider\Model\Queue\Consumer;

use Avalara\Excise\BaseProvider\Model\Queue\Consumer\DefaultConsumer;
use Psr\Log\LoggerInterface;
use Avalara\Excise\BaseProvider\Helper\Generic\Config as GenericConfig;
use Avalara\Excise\BaseProvider\Helper\Config as QueueConfig;
use Avalara\Excise\BaseProvider\Model\ResourceModel\Queue\CollectionFactory as QueueCollFactory;
use Avalara\Excise\BaseProvider\Framework\Rest\ApiClient as RestClient;

class ApiLogConsumer extends DefaultConsumer
{
    const CLIENT = 'api_log';
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var GenericConfig
     */
    protected $genericConfig;

    /**
     * @var QueueCollFactory
     */
    protected $queueCollFactory;

    /**
     * @var QueueConfig
     */
    protected $queueConfig;

    /**
     * @var RestClient
     */
    protected $restClient;

    /**
     * @var string
     */
    protected $client = self::CLIENT;

    /**
     * @var array
     */
    protected $allowedRestMethods = ["setClient", "restCall"]; 

    /**
     * @param LoggerInterface 
     * @param GenericConfig $genericConfig
     * @param QueueConfig $queueConfig
     * @param QueueCollFactory $queueCollFactory
     */
    public function __construct(
        LoggerInterface $logger,
        GenericConfig $genericConfig,
        QueueConfig $queueConfig,
        QueueCollFactory $queueCollFactory,
        RestClient $restClient
    ) {
        $this->logger = $logger;
        $this->genericConfig = $genericConfig;
        $this->restClient = $restClient;
        parent::__construct($logger, $queueConfig, $queueCollFactory);
    }

    /**
     * @inheritDoc
     */
    public function consume(\Avalara\Excise\BaseProvider\Api\Data\QueueInterface $queueJob)
    {
        $success = true;
        $response = [];
        $payload = $queueJob->getPayload();
        $payload = json_decode((string)$payload, true);
        $client = $this->restClient;
        if (count($payload) > 0) {
            foreach($payload as $method=>$arguments) {
                if (!in_array($method, $this->allowedRestMethods)) continue;
                try {
                    if ($method == 'restCall') {
                        $response = call_user_func_array([$client, $method], $arguments);
                        $response = json_decode(json_encode($response), true);
                        if (!(is_array($response) && array_key_exists("error", $response) && $response['error'] == null)) {
                            $success = false;
                        }
                    } else {
                        call_user_func_array([$client, $method], $arguments);
                    }
                } catch (\Exception $e) {
                    $response = [$e->getMessage()];
                    $this->logger->debug("API LOG Error Response : ".json_encode($response));
                    // code to add CEP logs for exception
                    try {
                        $functionName = __METHOD__;
                        $operationName = get_class($this);     
                        // @codeCoverageIgnoreStart           
                        $this->logger->logDebugMessage(
                            $functionName,
                            $operationName,
                            $e
                        );
                        // @codeCoverageIgnoreEnd
                    } catch (\Exception $e) {
                        //do nothing
                    }
                    // end of code to add CEP logs for exception
                    $success = false;
                }
            }
        }
        $response = json_encode($response);
        return [$success, $response];
    }

}
