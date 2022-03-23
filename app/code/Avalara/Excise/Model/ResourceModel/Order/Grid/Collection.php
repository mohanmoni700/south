<?php

namespace Avalara\Excise\Model\ResourceModel\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{

    /**
     * @var ResourceConnection
     */
    protected $resource;
    
    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param ResourceConnection $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        ResourceConnection $resource
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager);
        $this->resource = $resource;
    }

    protected function _renderFiltersBefore()
    {
        $salesOrderTable = $this->resource->getTableName('sales_order');
        $this->getSelect()->joinLeft(
            ["sales_order" => $salesOrderTable],
            'main_table.entity_id = sales_order.entity_id',
            ['sales_tax','excise_tax','base_shipping_tax_amount']
        )
        ->distinct();

        parent::_renderFiltersBefore();
    }

    // Below function fixes error and endless loading when filters lead to empty page
    protected function _initSelect()
    {
        $this->addFilterToMap('created_at', 'main_table.created_at');
        parent::_initSelect();
    }
}
