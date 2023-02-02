<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\ResourceModel\Report\Subscription\Product;

class Collection extends \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
{

    /**
     * Period format.
     *
     * @var string
     */
    protected $_periodFormat;

    /**
     * Selected columns.
     *
     * @var array
     */
    protected $_selectedColumns = [];

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory             $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Sales\Model\ResourceModel\Report                    $resource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface               $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactoryCollection,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyDb,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\ResourceModel\Report $resourceReport,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $resourceReport->init('md_subscribenow_aggregated_product');
        parent::__construct($entityFactoryCollection, $logger, $fetchStrategyDb, $eventManager, $resourceReport, $connection);
    }

    /**
     * Get selected columns.
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $connection = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $connection->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $connection->getDateExtractSql(
                'period',
                \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_YEAR
            );
        } else {
            $this->_periodFormat = $connection->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = [
                'period' => $this->_periodFormat,
                'product_name' => 'MAX(product_name)',
                'subscriber_count' => 'SUM(subscriber_count)',
                'active_subscriber' => 'SUM(active_subscriber)',
                'pause_subscriber' => 'SUM(pause_subscriber)',
                'cancel_subscriber' => 'SUM(cancel_subscriber)',
                'no_of_occurrence' => 'no_of_occurrence',
            ];
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns() + ['period' => $this->_periodFormat];
        }

        return $this->_selectedColumns;
    }

    /**
     * Apply custom columns before load.
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()->group([$this->_periodFormat, 'product_name']);
        }
        if ($this->isSubTotals()) {
            $this->getSelect()->group([$this->_periodFormat]);
        }

        return parent::_beforeLoad();
    }
}
