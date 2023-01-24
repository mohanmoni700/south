<?php
namespace Alfakher\SlopePayment\Controller\Adminhtml\Configuration;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\HTTP\Client\Curl;
use Alfakher\SlopePayment\Model\System\Config\Backend\Environment;
use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;
use Magento\Framework\Serialize\Serializer\Json;

class KeyValidate extends Action
{
    const TEST_API_ENDPOINT = '/customers/test';

    /**
    * @var Curl
    */
    protected $curl;

    /**
    * @var Json
    */
    protected $json;

    /**
     * Validate constructor.
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        SlopeConfigHelper $slopeConfig,
        Curl $curl,
        Json $json
    ) {
        $this->config = $slopeConfig;
        $this->curl = $curl;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $environment = $this->getRequest()->getParam('environment');
        $publicKey = $this->getRequest()->getParam('public_key');
        $privateKey = $this->getRequest()->getParam('private_key');

        if ($environment === Environment::ENVIRONMENT_SANDBOX) {
            $testEndpoint = $this->config->getSandboxApiEndpointUrl();
        } else {
            $testEndpoint = $this->config->getProductionApiEndpointUrl();
        }

        if (false !== strpos($publicKey, '*')) {
            if ($environment === Environment::ENVIRONMENT_SANDBOX) {
                $publicKey = $this->config->getSandboxPublicKey();
            } else {
                $publicKey = $this->config->getProductionPublicKey();
            }
        }

        if (false !== strpos($privateKey, '*')) {
            if ($environment === Environment::ENVIRONMENT_SANDBOX) {
            $privateKey = $this->config->getSandboxPrivateKey();
            } else {
            $privateKey = $this->config->getProductionPrivateKey();
            }
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        
        try {
            /* test endpoint to test connection */
            $url = $testEndpoint . self::TEST_API_ENDPOINT;

            /* prepare curl header,Credentials,options */
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->setCredentials($publicKey, $privateKey);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);

            /* call api using curl request */
            //get method
            $this->curl->get($url);
            //post method for reference
            //$this->curl->post($url, $params);
            $response = $this->curl->getBody();
            $response = $this->json->unserialize($response);

            $reStatusCode = $response['statusCode'];
            if ($reStatusCode != '401') {
                $result->setData(['success' => 'true', 'message' => 'Connected Successfully !!']);
            } else {
                $result->setData(['success' => 'false', 'message' => 'Connection is not valid !!']);
            }

        } catch (Exception $e) {
            $result->setData(['success' => 'false', 'message' => $e->getMessage()]);
        }

        return $result;
    }
}
