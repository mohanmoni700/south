<?php

namespace Avalara\Excise\Framework\Lib;

use GuzzleHttp\Client;
use Avalara\Excise\Framework\Constants;

/**
 * Base AvalaraClient object that handles connectivity to the Avalara v2 API server.
 * Is inherited by ApiClient class, all API calling functions should reside in the inherited class
 */
class AvalaraClient
{
    /**
     * @var Client     The Guzzle client to use to connect to AvaTax
     */
    protected $client;

    /**
     * @var array      The authentication credentials to use to connect to AvaTax
     */
    protected $auth;

    /**
     * @var string      The application name as reported to AvaTax
     */
    protected $appName;

    /**
     * @var string      The application version as reported to AvaTax
     */
    protected $appVersion;

    /**
     * @var string      The machine name as reported to AvaTax
     */
    protected $machineName;

    /**
     * @var string      The root URL of the AvaTax environment to contact
     */
    protected $environment;

    /**
     * @var bool        The setting for whether the client should catch exceptions
     */
    protected $catchExceptions;

    /**
     * @var Array  Additional headers
     */
    protected $additionalParams;

    /**
     * @var logger object
     */
    protected $logger;

    /**
     * Construct a new AvalaraClient
     *
     * @param string $appName      Specify the name of your application here.  Should not contain any semicolons.
     * @param string $appVersion Specify the version number of your application here. Should not contain any semicolons.
     * @param string $machineName Specify the machine name of the machine on which
     * this code is executing here.  Should not contain any semicolons.
     * @param string $environment  Indicates which server to use; acceptable values
     *  are "sandbox" or "production", or the full URL of your AvaTax instance.
     * @param string $type
     * @param array $guzzleParams  Extra parameters to pass to the guzzle HTTP
     * client (http://docs.guzzlephp.org/en/latest/request-options.html)
     *
     * @throws \Exception
     */
    public function __construct(
        $appName,
        $appVersion,
        $environment,
        $machineName = "",
        $type = Constants::EXCISE_API,
        $guzzleParams = []
    ) {
        // app name and app version are mandatory fields.
        if ($appName == "" || $appName == null || $appVersion == "" || $appVersion == null) {
            throw new \Exception('appName and appVersion are mandatory fields!');
        }

        // machine name is nullable, but must be empty string to avoid error when concat in client string.
        if ($machineName == null) {
            $machineName = "";
        }

        // assign client header params to current client object
        $this->appVersion = $appVersion;
        $this->appName = $appName;
        $this->machineName = $machineName;
        $this->environment = $environment;
        $this->catchExceptions = true;

        // Determine startup environment
        $env = $type == Constants::EXCISE_API ?
            Constants::ENV_EXCISE_PRODUCTION_BASE_URL : Constants::ENV_AVATAX_PRODUCTION_BASE_URL;
        if ($environment == Constants::API_MODE_DEV) {
            $env = $type == Constants::EXCISE_API ?
                Constants::ENV_EXCISE_SANDBOX_BASE_URL : Constants::ENV_AVATAX_SANDBOX_BASE_URL;
        } elseif ((substr($environment, 0, 8) == 'https://') || (substr($environment, 0, 7) == 'http://')) {
            $env = $environment;
        }

        $guzzleParams['base_uri'] = $env;

        // Configure the HTTP client
        $this->client = new Client($guzzleParams);
    }

    /**
     * Configure this client to use the specified username/password security settings
     *
     * @param  string          $username   The username for your AvaTax user account
     * @param  string          $password   The password for your AvaTax user account
     * @return AvalaraClient
     */
    public function withSecurity($username, $password)
    {
        $this->auth = [$username, $password];
        return $this;
    }

    /**
     * Configure this client to use Account ID / License Key security
     *
     * @param  int             $accountId      The account ID for your AvaTax account
     * @param  string          $licenseKey     The private license key for your AvaTax account
     * @return AvalaraClient
     */
    public function withLicenseKey($accountId, $licenseKey)
    {
        $this->auth = [$accountId, $licenseKey];
        return $this;
    }

    /**
     * Set additional headers
     *
     * @param  array $params
     * @return AvalaraClient
     */
    public function addtionalHeaders($params)
    {
        $this->additionalParams = $params;
        return $this;
    }

    /**
     * Configure this client to use bearer token
     *
     * @param  string          $bearerToken     The private bearer token for your AvaTax account
     * @return AvalaraClient
     */
    public function withBearerToken($bearerToken)
    {
        $this->auth = [$bearerToken];
        return $this;
    }

    /**
     * Configure this client to use Account ID / License Key security
     *
     * @param  int             $accountId      The account ID for your AvaTax account
     * @param  string          $licenseKey     The private license key for your AvaTax account
     * @return AvalaraClient
     */
    public function withBasicToken($accountId, $licenseKey)
    {
        // Third element '3' added to the array to support multiple authentication methods
        $this->auth = ['type', base64_encode($accountId . ":" . $licenseKey), '3'];
        return $this;
    }

    /**
     * Configure the client to either catch web request exceptions and return a message or throw the exception
     *
     * @param bool $catchExceptions
     * @return AvalaraClient
     */
    public function withCatchExceptions($catchExceptions = true)
    {
        $this->catchExceptions = $catchExceptions;
        return $this;
    }

    /**
     * Return the client object, for extended class(es) to retrive the client object
     *
     * @return AvalaraClient
     */
    public function getClient()
    {
        return $this;
    }

    /**
     * Make a single REST call to the AvaTax v2 API server
     *
     * @param string $apiUrl           The relative path of the API on the server
     * @param string $verb             The HTTP verb being used in this request
     * @param string|array $guzzleParams     The Guzzle parameters for this request,
     * including query string and body parameters
     * @codeCoverageIgnore
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    protected function restCall($apiUrl, $verb, $guzzleParams, $getArray = false)
    {
        $config = $this->client->getConfig();

        $requestUrl = $config['base_uri']->getHost() . $apiUrl;
        $message = "API " . $apiUrl;

        // Set authentication on the parameters
        if (count($this->auth) == 2) {
            if (!isset($guzzleParams['auth'])) {
                $guzzleParams['auth'] = $this->auth;
            }
            $guzzleParams['headers'] = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Avalara-Client' => "{$this->appName}; 
                                        {$this->appVersion}; 
                                        PhpRestClient; 18.12.0; 
                                        {$this->machineName}"
            ];
        } elseif (count($this->auth) == 3) {
            $guzzleParams['headers'] = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $this->auth[1]
            ];
        } else {
            $guzzleParams['headers'] = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->auth[0],
                'X-Avalara-Client' => "{$this->appName}; 
                                        {$this->appVersion}; 
                                        PhpRestClient; 18.12.0; 
                                        {$this->machineName}"
            ];
        }

        // pass additional headers
        if ($this->additionalParams) {
            foreach ($this->additionalParams as $key => $value) {
                $guzzleParams['headers'][$key] = $value;
            }
        }

        $logContext['request'] = var_export(
            [
                'url' => $requestUrl,
                'body' => !empty($guzzleParams['body']) ? $guzzleParams['body'] : ""
            ],
            true
        );

        // Contact the server
        try {
            $requestMsg = "Input request " . $apiUrl;
            $this->logger->info($requestMsg, $logContext);
            $response = $this->client->request($verb, $apiUrl, $guzzleParams);
            $body = $response->getBody();

            $logContext['result'] = "  Response " . json_encode(json_decode((string)$body), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $logContext['extra']['class'] = __METHOD__;
            $this->logger->info($message, $logContext);

            return json_decode((string)$body, $getArray);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            if (!$this->catchExceptions) {
                throw $e;
            }
            return $e->getMessage();
        }
    }
}
