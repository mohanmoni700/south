<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Controller\Adminhtml\Configuration;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Alfakher\SlopePayment\Model\System\Config\Backend\Environment;
use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Alfakher\SlopePayment\Model\Gateway\Request as GatewayRequest;

class KeyValidate extends Action
{
    const TEST_API_ENDPOINT = '/customers/test';

    /**
     * @var Json
     */
    protected $json;
    
    /**
     * Validate constructor.
     *
     * @param Action\Context $context
     * @param SlopeConfigHelper $slopeConfig
     * @param Json $json
     * @param GatewayRequest $gatewayRequest
     */
    public function __construct(
        Action\Context $context,
        SlopeConfigHelper $slopeConfig,
        Json $json,
        GatewayRequest $gatewayRequest
    ) {
        $this->config = $slopeConfig;
        $this->json = $json;
        $this->gatewayRequest = $gatewayRequest;
        parent::__construct($context);
    }

    /**
     * Validate Credentials
     *
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

            $response = $this->gatewayRequest->get($url);
            $response = $this->json->unserialize($response);

            $reStatusCode = $response['statusCode'];
            if ($reStatusCode != '401') {
                $result->setData(['success' => 'true']);
            } else {
                $result->setData(['success' => 'false']);
            }

        } catch (Exception $e) {
            $result->setData(['success' => 'false']);
        }

        return $result;
    }
}
