<?php
namespace HookahShisha\Customization\Model\ResourceModel\Grid;

use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

/**
 * Collection to present grid of customers on admin area
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Grid\Collection
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var TimezoneInterface
     */
    private $timeZone;


    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param ResolverInterface $localeResolver
     * @param string $mainTable
     * @param string $resourceModel
     * @param TimezoneInterface|null $timeZone
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        ResolverInterface $localeResolver,
        $mainTable = 'customer_grid_flat',
        $resourceModel = Customer::class,
        TimezoneInterface $timeZone = null
    ) {
        $this->localeResolver = $localeResolver;
        parent::__construct(
            $entityFactory, $logger, $fetchStrategy, $eventManager, $localeResolver, $mainTable, $resourceModel, $timeZone
        );
        $this->timeZone = $timeZone ?: ObjectManager::getInstance()
            ->get(TimezoneInterface::class);
    }


    /**
     * Add fulltext filter
     *
     * @param string $value
     * @return $this
     */
    public function addFullTextFilter(string $value)
    {
        
        $fields = $this->getFulltextIndexColumns();
        $whereCondition = '';

        foreach ($fields as $key => $field) {
            if ($field === 'company_name' || $field === 'legal_name') {
                $field = 'company.' . $field;
            } elseif ($field === 'billing_region') {
                $field = $this->getRegionNameExpression();
            } else {
                $field = 'main_table.' . $field;
            }
            $condition = $this->_getConditionSql(
                $this->getConnection()->quoteIdentifier($field),
                ['like' => "%$value%"]
            );
            $whereCondition .= ($key === 0 ? '' : ' OR ') . $condition;
        }

        if ($whereCondition) {
            $this->getSelect()->where($whereCondition);
        }
        return $this;
    }

    /**
     * Returns list of columns from fulltext index
     *
     * @return array
     */
    private function getFulltextIndexColumns(): array
    {
        $indexes = $this->getConnection()->getIndexList($this->getMainTable());
        $indexess = $this->getConnection()->getIndexList('company');
        
        $companyindex = $indexess['COMPANY_COMPANY_NAME_LEGAL_NAME'];
        $columnlists = $companyindex['COLUMNS_LIST'];

        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                $columnlist = array_merge($columnlists, $index['COLUMNS_LIST']);
            } else {
                $columnlist = [];
            }
        }
        return $columnlist;
    } 

    /**
     * Get SQL Expression to define Region Name field by locale
     *
     * @return \Zend_Db_Expr
     */
    private function getRegionNameExpression(): \Zend_Db_Expr
    {
        $connection = $this->getConnection();
        $defaultNameExpr = $connection->getIfNullSql(
            $connection->quoteIdentifier('rct.default_name'),
            $connection->quoteIdentifier('main_table.billing_region')
        );

        return $connection->getIfNullSql(
            $connection->quoteIdentifier('rnt.name'),
            $defaultNameExpr
        );
    }
}
