<?php
declare(strict_types=1);

namespace Corra\NewRelicReportingGraphql\Model\DataProvider;

use GraphQL\Type\SchemaConfig;
use Magento\Framework\GraphQl\Schema;
use Magento\Framework\Serialize\SerializerInterface;

class NewRelicDataProvider
{
    protected const PREFIX = '/GraphQl/Controller/GraphQl/';
    protected const BACKSLASH = '/';

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * NewRelicDataProvider constructor.
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(SerializerInterface $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Get Data Method
     *
     * @param Schema $schema
     * @return array
     */
    public function getData(Schema $schema)
    {
        if (!$schema) {
            return [];
        }
        $schemaConfig = $schema->getConfig();
        if (!$schemaConfig) {
            return [];
        }
        if ($schemaConfig->getMutation()->getFields()) {
            $callType = 'Mutation';
            $transactionDetails = $this->getMutationDetails($schemaConfig);
        } elseif ($schemaConfig->getQuery()->getFields()) {
            $callType = 'Query';
            $transactionDetails = $this->getQueryDetails($schemaConfig);
        } else {
            return [];
        }
        return [
            'transactionName' => $this->buildTransactionName($callType, $transactionDetails)
        ];
    }

    /**
     * Get Mutation Details Method
     *
     * @param SchemaConfig $schemaConfig
     * @return int|string
     */
    private function getMutationDetails(SchemaConfig $schemaConfig)
    {
        return $this->getOperationName($schemaConfig->getMutation()->getFields());
    }

    /**
     * Get Schema Config Method
     *
     * @param SchemaConfig $schemaConfig
     * @return int|string
     */
    private function getQueryDetails(SchemaConfig $schemaConfig)
    {
        return $this->getOperationName($schemaConfig->getQuery()->getFields());
    }

    /**
     * Get Operation Name Method
     *
     * @param array $details
     * @return int|string|null
     */
    private function getOperationName(array $details)
    {
        return array_key_first($details);
    }

    /**
     * Build a transaction name based on query type and operation name format:
     * /GraphQl/Controller/GraphQl/{(Query|Mutation)}/{name}
     *
     * @param string $callType
     * @param string $operationName
     * @return string
     */
    private function buildTransactionName(string $callType, string $operationName)
    {
        return self::PREFIX . $callType . self::BACKSLASH . $operationName;
    }
}
