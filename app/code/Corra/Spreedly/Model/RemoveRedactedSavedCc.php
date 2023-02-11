<?php
declare(strict_types=1);

namespace Corra\Spreedly\Model;

use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Corra\Spreedly\Gateway\Config\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class RemoveRedactedSavedCc
{
    const TABLE_NAME = 'vault_payment_token';

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Curl $curl
     * @param Json $json
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Config $config
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        Json $json,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Config $config,
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->config = $config;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * @param $token
     * @return string
     */
    public function getUrl($token)
    {
        return $this->config->getServiceUrl() . 'payment_methods/' . $token->getGatewayToken() . '.json';
    }

    /**
     * @return void
     */
    public function execute()
    {
        $start = microtime(true);
        $this->logger->info(__('Start removing the Redacted saved card'));

        $searchCriteria = $this->buildSearchCriteria();
        $searchResult = $this->paymentTokenRepository->getList($searchCriteria);
        $tokens = $searchResult->getItems();

        $entityIds = [];
        foreach ($tokens as $token) {
            $this->curl->setCredentials(
                $this->config->getEnvironmentKey(),
                $this->config->getEnvironmentSecretKey()
            );

            $this->curl->get($this->getUrl($token));
            $this->curl->addHeader('content-type', 'application/json');

            $data = $this->curl->getBody();
            $jsonDecode = $this->json->unserialize($data);

            if (isset($jsonDecode['payment_method']) && $jsonDecode['payment_method']['storage_state'] == 'redacted') {
                $this->paymentTokenRepository->delete($token);
                $this->logger->info(__(sprintf('Removed Redacted saved card %s', $token->getGatewayToken()))->getText());
            }
            $entityIds[$token->getEntityId()] = $token->getEntityId();
        }

        $this->connection->update(
            $this->resource->getTableName(self::TABLE_NAME),
            ['is_redacted_checked' => 1],
            ['entity_id IN(?)' => $entityIds]
        );

        $this->logger->info(__(sprintf('Completed removing the Redacted saved card in %s sec, please check the log file debug.log', round(microtime(true) - $start, 2)))->getText());
    }

    /**
     * @return SearchCriteriaInterface
     */
    private function buildSearchCriteria(): SearchCriteriaInterface
    {
        $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::CUSTOMER_ID,
            null,
            'neq'
        );
        $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::IS_VISIBLE,
            1
        );
        $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::IS_ACTIVE,
            1
        );
        $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::PAYMENT_METHOD_CODE,
            'spreedly'
        );
        $this->searchCriteriaBuilder->addFilter(
            'is_redacted_checked',
            0
        );

        $creationReverseOrder = $this->sortOrderBuilder
            ->setField(PaymentTokenInterface::CREATED_AT)
            ->setDescendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($creationReverseOrder);

        $this->searchCriteriaBuilder->setPageSize(200);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $searchCriteria;
    }
}
