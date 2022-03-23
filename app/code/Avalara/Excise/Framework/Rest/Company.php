<?php

namespace Avalara\Excise\Framework\Rest;

use Avalara\Excise\Api\Rest\CompanyInterface;
use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Framework\Rest;
use Magento\Framework\DataObject;

class Company extends Rest implements CompanyInterface
{
    /**
     * @param \Avalara\Excise\Framework\AvalaraClientWrapper $client
     * @param DataObject|null  $request
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    protected function getCompaniesFromAvalarAccount($client, $request = null)
    {
        if ($request === null) {
            $request = $this->dataObjectFactory->create();
        }

        $clientResult = null;

        try {
            $clientResult = $client->queryAvataxCompanies(
                $request->getData('include'),
                $request->getData('filter'),
                $request->getData('top'),
                $request->getData('skip'),
                $request->getData('order_by')
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $this->handleException($clientException, $request);
        }

        return $this->formatResult($clientResult)->getData('value');
    }

    /**
     * @param \Avalara\Excise\Framework\AvalaraClientWrapper $client
     * @param DataObject|null $request
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    protected function getCompaniesFromExciseAccount($client, $request = null)
    {
        if ($request === null) {
            $request = $this->dataObjectFactory->create();
        }

        $clientResult = null;

        try {
            $clientResult = $client->queryExciseCompanies(
                $request->getData('effectiveDate')
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $this->handleException($clientException, $request);
        }
        
        if (is_array($clientResult)) {
            foreach ($clientResult as $idx => $res) {
                if ($res->IsActive != 1 || $res->HasAvaTaxExcise != 1) {
                    unset($clientResult[$idx]);
                }
            }
        }
        
        return $this->formatResult($clientResult);
    }

    /**
     * {@inheritDoc}
     */
    public function getCompanies(
        $request = null,
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        $client = $this->getClient($type, $scopeId, $scopeType);

        if ($type == Constants::AVALARA_API) {
            return $this->getCompaniesFromAvalarAccount($client, $request);
        }
        return $this->getCompaniesFromExciseAccount($client, $request);
    }

    /**
     * @param string          $accountNumber
     * @param string          $password
     * @param DataObject|null $request
     * @param bool|null       $isProduction
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvataxConnectionException
     */
    public function getCompaniesWithSecurity(
        $type,
        $accountNumber,
        $password,
        $request = null,
        $isSandbox = null,
        $scopeId = null,
        $scopeType = null
    ) {
        $this->isSandboxMode = $isSandbox;
        // Override security credentials with custom ones
        $this->setCredentials($accountNumber, $password);
        $client = $this->getClient($type);
        $client->withCatchExceptions(false);
        $client = $this->setAuthentication($scopeId, $scopeType, $client);

        if ($type == Constants::AVALARA_API) {
            return $this->getCompaniesFromAvalarAccount($client, $request);
        }
        return $this->getCompaniesFromExciseAccount($client, $request);
    }
}
