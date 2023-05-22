<?php

namespace Avalara\Excise\Framework\Interaction\Rest;

use Avalara\Excise\Api\RestAddressInterface;
use Avalara\Excise\Framework\AvalaraClientWrapperFactory;
use Avalara\Excise\Framework\Interaction\MetaData\MetaDataObjectFactory;
use Avalara\Excise\Framework\Interaction\Rest\Address\Result as AddressResult;
use Avalara\Excise\Framework\Rest;
use Avalara\Excise\Exception\AddressValidateException;
use Avalara\Excise\Exception\AvalaraConnectionException;
use Avalara\Excise\Framework\Interaction\Address\Validation as ValidationInteraction;
use Avalara\Excise\Framework\Interaction\Rest\Address\ResultFactory as AddressResultFactory;
use Avalara\Excise\Framework\Interaction\Rest\ClientPool;
use Avalara\Excise\Helper\Config;
use Avalara\Excise\Helper\Rest\Config as RestConfig;
use Avalara\Excise\Helper\Config as ConfigHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Avalara\Excise\Logger\ExciseLogger as LoggerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Avalara\Excise\Framework\Constants;

    /**     
    * @codeCoverageIgnore
    */
class Address extends Rest implements RestAddressInterface
{
    /**
     * @var RestConfig
     */
    protected $restConfig;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var \Avalara\Excise\Framework\Interaction\MetaData\MetaDataObject
     */
    protected $metaDataObject;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var AddressResultFactory
     */
    protected $addressResultFactory;

    /**
     * @param PhpSerialize $phpSerialize
     * @param CacheInterface $cache
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param ClientPool $clientPool
     * @param RestConfig $restConfig
     * @param AddressResultFactory $addressResultFactory
     * @param AvalaraClientWrapperFactory $clientWrapperFactory
     * @param ConfigHelper $config
     */
    public function __construct(
        Json $json,
        CacheInterface $cache,
        MetaDataObjectFactory $metaDataObjectFactory,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ClientPool $clientPool,
        RestConfig $restConfig,
        AddressResultFactory $addressResultFactory,
        AvalaraClientWrapperFactory $clientWrapperFactory,
        ConfigHelper $config,
        Rest $exciseClient
    ) {
        $this->json = $json;
        $this->cache = $cache;
        $this->metaDataObject = $metaDataObjectFactory->create(
            ['metaDataProperties' => \Avalara\Excise\Framework\Interaction\Address::$validFields]
        );
        parent::__construct($logger, $dataObjectFactory, $config, $clientWrapperFactory);
        $this->restConfig = $restConfig;
        $this->addressResultFactory = $addressResultFactory;
        $this->exciseClient = $exciseClient;
        $this->loggerapi = $logger;
    }

    /**
     * Perform REST request to validate address
     *
     * @param \Magento\Framework\DataObject $request
     * @param bool|null                     $isProduction
     * @param string|int|null               $scopeId
     * @param string|null                   $scopeType
     *
     * @return \Avalara\Excise\Framework\Interaction\Rest\Address\Result
     * @throws AddressValidateException
     * @throws AvalaraConnectionException
     */
    public function validate(
        $request,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        $addressCacheKey = $this->getCacheKey($request->getAddress()) . $scopeId;
        $cacheData = $this->cache->load($addressCacheKey);
        try {
            $validateResult = !empty($cacheData) ? $this->json->unserialize($cacheData) : '';
            if (isset($validateResult[0]) && !empty($validateResult[0])) {
                $validateResult = $validateResult[0];
            }
        } catch (\Throwable $exception) {
            $validateResult = '';
        }
        if ($validateResult instanceof AddressResult) {
            $this->logger->addInfo('Loaded address validate result from cache.', [
                'request' => var_export($request->getData(), true),
                'result' => var_export($validateResult->getData(), true),
                'cache_key' => $addressCacheKey
            ]);
            return $validateResult;
        } elseif (is_array($validateResult)
                && isset($validateResult['message'])
                && isset($validateResult['class'])) {
            $exceptionClass = $validateResult['class'];
            throw new $exceptionClass(__($validateResult['message']));
        }

        $client = $this->getClient(Constants::AVALARA_API, $scopeId, $scopeType);
        $client->withCatchExceptions(false);
        $client = $this->setAuthentication($scopeId, $scopeType, $client);

        $address = $request->getAddress();
        $textCase = $request->getTextCase();
        try {
             $resultObj = $client->resolveAddress(
                 $address->getLine1(),
                 $address->getLine2(),
                 $address->getLine3(),
                 $address->getCity(),
                 $address->getRegion(),
                 $address->getPostalCode(),
                 $address->getCountry(),
                 $textCase
             );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            // code to add CEP logs for exception
            try {
                $functionName = "validate";
                $operationName = "Framework_Interaction_Rest_Address_Resolve";
                $source = "addresses_resolve";
                // @codeCoverageIgnoreStart
                $this->loggerapi->logDebugMessage(
                    $functionName,
                    $operationName,
                    $clientException,
                    $source,
                    $scopeId,
                    $scopeType
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
            $this->handleException($clientException, $request);
        } catch (\Throwable $exception) {
            // code to add CEP logs for exception
            try {
                $functionName = "validate";
                $operationName = "Framework_Interaction_Rest_Address_Resolve";
                $source = "addresses_resolve";
                // @codeCoverageIgnoreStart
                $this->loggerapi->logDebugMessage(
                    $functionName,
                    $operationName,
                    $exception,
                    $source,
                    $scopeId,
                    $scopeType
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
            throw $exception;
        }

         $this->validateResult($resultObj, $request);
         $resultGeneric = $this->formatResult(json_decode(json_encode($resultObj), true));
        /** @var \Avalara\Excise\Framework\Interaction\Rest\Address\Result $result */
        $result = $this->addressResultFactory->create(['data' => $resultGeneric]);

        if (($result->hasData('validatedAddresses')) && (is_array($result->getData('validatedAddresses')))):
            $validatedAddressesData= [];
            $validatedAddresses = $this->addressResultFactory->create();

            $countyValue = '';
            if (($result->hasData('taxAuthorities')) && (is_array($result->getData('taxAuthorities')))):
                foreach ($result->getData('taxAuthorities') as $key => $value):
                    if (isset($value['jurisdictionType']) && $value['jurisdictionType']=="County") {
                        $countyValue = isset($value['jurisdictionName']) ? $value['jurisdictionName'] : '';
                    }
                endforeach;
            endif;

            foreach ($result->getData('validatedAddresses') as $key => $value):
                $value['county']= $countyValue;
                $validatedAddressesData[] = $validatedAddresses->setDataUsingMethod($key, $value);
            endforeach;

            $result->setData('validated_addresses', $validatedAddressesData);
            $serializedValidateResult = $this->json->serialize($result);
            $this->cache->save($serializedValidateResult, $addressCacheKey, [Config::AVALARA_CACHE_TAG]);
        endif;
        return $result;
    }

    /**
     * @param $result
     * @param null $request
     * @throws AddressValidateException
     */
    protected function validateResult($result, $request = null)
    {
        $errors = [];
        $warnings = [];
        if (isset($result->messages) && is_array($result->messages)) {
            foreach ($result->messages as $message) {
                if (in_array($message->severity, $this->restConfig->getAddressErrorSeverityLevels())) {
                    $errors[] = $message->summary;
                } elseif (in_array($message->severity, $this->restConfig->getAddressWarningSeverityLevels())) {
                    $warnings[] = $message->summary;
                }
            }
        }

        if (!empty($warnings)) {
            $warningsMsg = implode('; ', $warnings);

            $this->logger->warning(__('AvaTax address validation warnings: %1', $warningsMsg), [
                'request' => ($request!==null) ? var_export($request->getData(), true) : null,
                'result' => var_export($result, true),
            ]);
        }

        if (!empty($errors)) {
            $errorsMsg = implode('; ', $errors);

            $this->logger->error(__('AvaTax address validation errors: %1', $errorsMsg), [
                'request' => ($request!==null) ? var_export($request->getData(), true) : null,
                'result' => var_export($result, true),
            ]);

            throw new AddressValidateException(__($errorsMsg));
        }
    }
    /**
     * Create cache key by calling specified methods and concatenating and hashing
     *
     * @param $object
     * @return string
     * @throws LocalizedException
     */
    protected function getCacheKey($object)
    {
        return $this->metaDataObject->getCacheKeyFromObject($object);
    }
}
