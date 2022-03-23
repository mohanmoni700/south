<?php
/**
 * Avalara_Excise
 *
 * @copyright  Copyright (c) 2021 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Avalara\Excise\Framework\Rest;

use Avalara\Excise\Api\Rest\ListEntityUseCodesInterface;
use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Framework\Rest;
use Magento\Framework\DataObject;

/**
 * Fetch entity use codes from the API
 */
class ListEntityUseCodes extends Rest implements ListEntityUseCodesInterface
{

    /**
     * {@inheritDoc}
     */
    public function getEntityUseCodes(
        $request = null,
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        $accountNumber = $this->config->getAvaTaxAccountNumber($scopeId, $scopeType);
        try {
            $client = $this->getClient($type, $scopeId, $scopeType);
            $client = $this->setAvataxCredentials($scopeId, $scopeType, $client);
            $result = $this->getEntityUseCodesWithSecurity($client, $accountNumber, $request, $scopeId, $scopeType);
            $resultArray = $this->convertToOptions($result);
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $this->handleException($clientException);
        }
        return $resultArray;
    }

    /**
     * [getEntityUseCodesWithSecurity description]
     *
     * @return  [type]  [return description]
     */
    public function getEntityUseCodesWithSecurity(
        $client,
        $accountNumber,
        $request = null,
        $scopeId = null,
        $scopeType = null
    ) {
        $currentMode = $this->config->getCurrentModeString($scopeId, $scopeType);
        $isSandbox = true;
        if ($currentMode == Constants::API_MODE_PROD) {
            $isSandbox = false;
        }
        $this->isSandboxMode = $isSandbox;
        // Override security credentials with custom ones
        $client = $this->setAvataxCredentials($scopeId, $scopeType, $client);
        $client = $this->setAuthentication($scopeId, $scopeType, $client);

        return $this->getEntityUseCodesList($client, $request);
    }

    /**
     * @param \Avalara\Excise\Framework\AvalaraClientWrapper $client
     * @param DataObject|null  $request
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    protected function getEntityUseCodesList($client, $request = null)
    {
        if ($request === null) {
            $request = $this->dataObjectFactory->create();
        }

        $clientResult = null;

        try {
            $clientResult = $client->queryEntityUseCodes(
                $request->getData('include'),
                $request->getData('filter'),
                $request->getData('top'),
                $request->getData('skip'),
                $request->getData('order_by')
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $this->handleException($clientException, $request);
        }
        if (is_object($this->formatResult($clientResult))) {
            return $this->formatResult($clientResult)->getData('value');
        }
         return [];
    }

    /**
     * Convert array to dropdown options.
     *
     * @param   array  $result
     *
     * @return  array
     */
    protected function convertToOptions($result)
    {
        $optionArr[] = ['label' => 'NONE', 'value'=> 'NONE'];
        if (!is_array($result) || empty($result)) {
            return $optionArr;
        }

        foreach ($result as $value) {
            $optionArr[] = [
                'label' => $value['code'] .' - '. $value['name'] ,
                'value' => $value['code']
            ];
        }
        return $optionArr;
    }
}
