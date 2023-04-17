<?php

namespace Avalara\Excise\Framework;

use Avalara\Excise\Framework\Lib\AvalaraClient;
use Avalara\Excise\Framework\Constants;

/**
 * Contains the actual API calls
 * @codeCoverageIgnore
 */
class ApiClient extends AvalaraClient
{
    /**
     * Test the API connection
     *
     * @param string|null $path
     * @return FetchResult
     */
    public function ping($path)
    {
        if (empty($path)) {
            $path = Constants::API_V1_EXCISE_PING_ENDPOINT;
        }
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create Tax transaction
     *
     * @param string|null $path
     * @return FetchResult
     */
    public function createTaxTransaction($companyId, $payload)
    {
        $path = Constants::API_V1_EXCISE_CREATE_TRANSACTION_ENDPOINT;
        $company['x-company-id'] = $companyId;
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($payload)
        ];

        $this->addtionalHeaders($company);
        return $this->restCall($path, 'POST', $guzzleParams, true);
    }

    /**
     * Retrieve all companies
     *
     * Get multiple company objects.
     *
     * A `company` represents a single corporation or individual that is registered to handle transactional taxes.
     *
     * Search for specific objects using the criteria in the `$filter` parameter;
     * full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * You may specify one or more of the following values in the `$include` parameter
     * to fetch additional nested data, using commas to separate multiple values:
     *
     * * Contacts
     * * Items
     * * Locations
     * * Nexus
     * * Settings
     * * TaxCodes
     * * TaxRules
     * * UPC
     *
     * @param string $include A comma separated list of objects to fetch underneath this company.
     *     Any object with a URL path underneath this company can be fetched by specifying its name.
     * @param string $filter A filter statement to identify specific records to retrieve.
     *     For more information on filtering, see [Filtering in REST]
     *       (http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results.
     *      Used with `$skip` to provide pagination for large datasets. Unless otherwise specified,
     *      the maximum number of records that can be returned from an API call is 1,000 records.
     * @param int $skip If nonzero, skip this number of results before returning data.
     *      Used with `$top` to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`,
     *      for example `id ASC`.
     * @return FetchResult
     */
    public function queryAvataxCompanies($include = null, $filter = null, $top = null, $skip = null, $orderBy = null)
    {
        $path = Constants::API_V2_AVATAX_COMPANY_LIST_ENDPOINT;
        $guzzleParams = [
            'query' => [
                '$include' => $include,
                '$filter' => $filter,
                '$top' => $top,
                '$skip' => $skip,
                '$orderBy' => $orderBy
            ],
            'body' => null
        ];

        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all companies
     *
     * Get multiple company objects.
     *
     * A `company` represents a single corporation or individual that is registered to handle transactional taxes.
     *
     * @param string|null $path
     * @param string $filteeffectiveDate A filter statement to identify specific records to retrieve.
     *
     * @return FetchResult
     */
    public function queryExciseCompanies($effectiveDate)
    {
        $effectiveDate = empty($effectiveDate) ? Constants::DEFAULT_EFFECTIVE_DATE : $effectiveDate;
        $path = Constants::API_V1_EXCISE_COMPANY_LIST_ENDPOINT1 . Constants::API_V1_EXCISE_COMPANY_LIST_ENDPOINT2;
        $guzzleParams = [
            'query' => ['effectiveDate' => $effectiveDate],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Send txn details to Excise Logger API
     *
     * @param array $params
     * @param string $connectorId
     * @param string $host
     * @return FetchResult
     */
    public function logData($params, $connectorId, $host)
    {
        $path = Constants::API_LOGGER_ENDPOINT . $connectorId;
        $guzzleParams = [
            //'Host' => $host,
            'query' => [],
            'body' => json_encode($params)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Create Commit Transaction
     *
     * @param string|null $companyId
     * @param string|null $tarnsactionId
     * @return FetchResult
     */
    public function commitTransaction($companyId, $tarnsactionId)
    {
        $path = Constants::API_V1_EXCISE_TRANSACTION;
        $path = $path . $tarnsactionId . Constants::API_V1_EXCISE_TRANSACTION_COMMIT_ENDPOINT;
        $company['x-company-id'] = $companyId;
        $guzzleParams = [];

        $this->addtionalHeaders($company);
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * @param $line1
     * @param $line2
     * @param $line3
     * @param $city
     * @param $region
     * @param $postalCode
     * @param $country
     * @param $textCase
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function resolveAddress($line1, $line2, $line3, $city, $region, $postalCode, $country, $textCase)
    {
        $path = Constants::API_V2_AVATAX_ADDRESS_VALIDATION_ENDPOINT;
        $guzzleParams = [
            'body' => json_encode([
                'line1' => $line1,
                'line2' => $line2,
                'line3' => $line3,
                'city' => $city,
                'region' => $region,
                'postalCode' => $postalCode,
                'country' => $country
                //'textCase' => $textCase
            ]),
            'query' => null
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Fetch EntityUseCodes from the API
     *
     * @param   [type]  $include  [$include description]
     * @param   [type]  $filter   [$filter description]
     * @param   [type]  $top      [$top description]
     * @param   [type]  $skip     [$skip description]
     * @param   [type]  $orderBy  [$orderBy description]
     *
     * @return  [type]            [return description]
     */
    public function queryEntityUseCodes($include = null, $filter = null, $top = null, $skip = null, $orderBy = null)
    {
        $path = Constants::API_V2_AVATAX_ENTITY_USE_CODES_ENDPOINT;
        $guzzleParams = [
            'query' => [
                '$include' => $include,
                '$filter' => $filter,
                '$top' => $top,
                '$skip' => $skip,
                '$orderBy' => $orderBy
            ],
            'body' => null
        ];

        return $this->restCall($path, 'GET', $guzzleParams);
    }
}
