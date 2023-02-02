<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\ResourceModel\Order\ProductSubscribers\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'md_subscribenow_product_subscribers',
        $resourceModel = \Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        $this
            ->getSelect()
            ->join(
                ['associated_orders' => $this->getTable('md_subscribenow_product_associated_orders')],
                'associated_orders.subscription_id = main_table.subscription_id',
                ''
            )
            ->join(
                ['sales_order' => $this->getTable('sales_order')],
                'sales_order.increment_id = associated_orders.order_id',
                'entity_id AS order_id'
            );

        parent::_initSelect();
    }

    /**
     * becuase sales_order_view has parameter labelled order_id, but in our table, name of column is sales_order.entity_id
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'order_id') {
            $field = 'sales_order.entity_id';
        }

        $this->_filterCache[$field] = $condition;
        return parent::addFieldToFilter($field, $condition);
    }

    public function addOrderFilter($order_id)
    {
        return $this->addFieldToFilter('order_id', $order_id);
    }
}
