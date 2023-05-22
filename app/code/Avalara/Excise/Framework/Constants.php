<?php

namespace Avalara\Excise\Framework;

/**
 * Common class for holding values
 */
class Constants
{
    /**
     * Excise API name constant
     */
    const EXCISE_API_NAME = 'AvaTax Excise';

    /**
     * Avalara Address API name constant
     */
    const AVALARA_API_NAME = 'AvaTax';

    /**
     * String value of API mode
     */
    const API_MODE_PROD = 'production';

    /**
     * String value of API mode
     */
    const API_MODE_DEV = 'sandbox';

    /**
     * Production API URL
     */
    const ENV_AVATAX_PRODUCTION_BASE_URL = 'https://rest.avatax.com';

    /**
     * Sandbox API URL
     */
    const ENV_AVATAX_SANDBOX_BASE_URL = 'https://sandbox-rest.avatax.com';

    /**
     * Sandbox API URL for Excise Tax
     */
    const ENV_EXCISE_SANDBOX_BASE_URL = 'https://excisesbx.avalara.com';

    /**
     * Production API URL for Excise Tax
     */
    const ENV_EXCISE_PRODUCTION_BASE_URL = 'https://excise.avalara.com';

    /**
     * Endpoint to ping Excise API
     */
    const API_V1_EXCISE_PING_ENDPOINT = '/api/v1/utilities/ping';

    /**
     * Endpoint to ping Excise API
     */
    const API_V2_AVATAX_PING_ENDPOINT = '/api/v2/utilities/ping';

    /**
     * Endpoint to create Tax Transaction API
     */
    const API_V1_EXCISE_CREATE_TRANSACTION_ENDPOINT = '/api/v1/AvaTaxExcise/transactions/create';

    /**
     * Endpoint to create Transaction API
     */
    const API_V1_EXCISE_TRANSACTION = '/api/v1/AvaTaxExcise/transactions/';
    /**
     * Endpoint to create Transaction API
     */
    const API_V1_EXCISE_TRANSACTION_COMMIT_ENDPOINT = '/commit';

    /**
     * API Type
     */
    const EXCISE_API = 'tax';

    /**
     * API Type
     */
    const AVALARA_API = 'address';

    /**
     * Current Avarala_Excise module version
     */
    const APP_VERSION = '2.2.0';

    /**
     * Endpoint to get the company list under an account
     */
    const API_V2_AVATAX_COMPANY_LIST_ENDPOINT = '/api/v2/companies';

    /**
     * Default value for the effective date filter
     */
    const DEFAULT_EFFECTIVE_DATE = '01/01/1900';

    /**
     * Endpoint to get the company list under an account
     */
    const API_V1_EXCISE_COMPANY_LIST_ENDPOINT1 = '/api/v1/Utilities/GetMast';

    const API_V1_EXCISE_COMPANY_LIST_ENDPOINT2 = 'erCompanies';

    const API_LOG_TYPE_PERFORMANCE = 'performance';
    const API_LOG_TYPE_DEBUG = 'debug';
    const API_LOG_TYPE_CONFIG = 'config';
    
    const API_LOG_LEVEL_ERROR = 'error';
    const API_LOG_LEVEL_EXCEPTION = 'exception';
    const API_LOG_LEVEL_INFO = 'info';

    /**
     * Api Log Types
     */
    const API_LOG_TYPE = [
        self::API_LOG_TYPE_PERFORMANCE => 'Performance', 
        self::API_LOG_TYPE_DEBUG => 'Debug', 
        self::API_LOG_TYPE_CONFIG => 'ConfigAudit'
    ];

    /**
     * Api Log Levels
     */
    const API_LOG_LEVEL = [
        self::API_LOG_LEVEL_ERROR => 'Error', 
        self::API_LOG_LEVEL_EXCEPTION => 'Exception', 
        self::API_LOG_LEVEL_INFO => 'Informational'
    ];
    
    /**
     * Sandbox API URL for LOGGER
     */
    const ENV_LOGGER_SANDBOX_BASE_URL = 'https://ceplogger.sbx.avalara.com';

    /**
     * Production API URL for LOGGER
     */
    const ENV_LOGGER_PRODUCTION_BASE_URL = 'https://ceplogger.avalara.com';

    /**
     * Endpoint for logger
     */
    const API_LOGGER_ENDPOINT = '/api/logger/';

    /**
     * Connector Id in ATE
     */
    const CONNECTOR_ID = "a0o5a000007hkt0AAA";

    /**
     * Source System in Tax Transaction API
     */
    const SOURCE_SYSTEM = 'Magento for Tobacco || '.self::APP_VERSION.'v1'; 

    /**
     * Pick time in Tax COMMIT Transaction API
     */
    const COMMIT_API_PICK_TIME = '2';

    /**
     * Maximum attempts in Tax COMMIT Transaction API
     */
    const COMMIT_TRANSACTION_MAX_ATTEMPTS = '3';

    /**
     * Endpoint for address validation
     */
    const API_V2_AVATAX_ADDRESS_VALIDATION_ENDPOINT = "/api/v2/addresses/resolve";

    /**
     * Set SHIPPING LINE ITEM ID
     */
    const SHIP_LINE_NO = "999999999";

    /**
     * Endpoint to get the EntityuseCodes list from Excise
     */
    const API_V2_AVATAX_ENTITY_USE_CODES_ENDPOINT = '/api/v2/definitions/entityusecodes';

    /**
     * @var config path for customer type
     */
    const CUSTOMER_TYPE_PATH = 'tax/avatax_excise/transaction_type';

    /**
     * @var Default value for customer type
     */
    const CUSTOMER_TYPE_DEFAULT = 'DIRECT';
}
