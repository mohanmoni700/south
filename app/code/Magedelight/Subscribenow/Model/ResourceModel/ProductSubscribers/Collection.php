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

namespace Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_eventPrefix = 'md_product_subscribers_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'md_product_subscribers_collection';

    /**
     * @var string
     */
    protected $_idFieldName = 'subscription_id';

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface        $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface       $eventManager
     * @param StoreManagerInterface  $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param AbstractDb             $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * Model initialization.
     */
    protected function _construct()
    {
        $this->_init('Magedelight\Subscribenow\Model\ProductSubscribers', 'Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers');
    }
}
