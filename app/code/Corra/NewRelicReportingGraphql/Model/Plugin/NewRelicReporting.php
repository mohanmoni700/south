<?php
declare(strict_types=1);

namespace Corra\NewRelicReportingGraphql\Model\Plugin;

use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema;
use Corra\NewRelicReportingGraphql\Model\DataProvider\NewRelicDataProvider;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

class NewRelicReporting
{
    /**
     * @var NewRelicDataProvider
     */
    private $newRelicDataProvider;

    /**
     * @var NewRelicWrapper
     */
    private $newRelicWrapper;

    /**
     * NewRelicReporting constructor.
     * @param NewRelicDataProvider $newRelicDataProvider
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        NewRelicDataProvider $newRelicDataProvider,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->newRelicDataProvider = $newRelicDataProvider;
        $this->newRelicWrapper = $newRelicWrapper;
    }

    /**
     * Before Plugin
     *
     * @param QueryProcessor $subject
     * @param Schema $schema
     * @param string $source
     * @param ContextInterface|null $contextValue
     * @param array|null $variableValues
     * @param string|null $operationName
     * @return array|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcess(
        QueryProcessor $subject,
        Schema $schema,
        string $source,
        ContextInterface $contextValue = null,
        array $variableValues = null,
        string $operationName = null
    ) {
        $newRelicTransactionData = $this->newRelicDataProvider->getData($schema);
        if (empty($newRelicTransactionData)) {
            return;
        }
        $this->newRelicWrapper->setTransactionName($newRelicTransactionData['transactionName']);

        return [$schema, $source, $contextValue, $variableValues, $operationName];
    }
}
