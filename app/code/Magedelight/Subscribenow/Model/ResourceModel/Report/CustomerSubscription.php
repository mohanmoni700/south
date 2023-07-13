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

namespace Magedelight\Subscribenow\Model\ResourceModel\Report;

use DateTime;
use Exception;
use Magedelight\Subscribenow\Model\Flag;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Report\AbstractReport;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

/**
 * CustomerSubscription report resource model.
 *
 * @SuppressWarnings(PHPMagedelight.CouplingBetweenObjects)
 */
class CustomerSubscription extends AbstractReport
{
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        Validator $timezoneValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        ResourceConnection $resource,
        CollectionFactory $collectionFactory,
        AttributeRepositoryInterface $attributeRepositoryInterface,
        TimezoneInterface $timezone,
        $connectionName = null
    ) {
        if (!$connectionName) {
            $connectionName = 'sales';
        }

        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );

        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->attributeRepositoryInterface = $attributeRepositoryInterface;
        $this->timezone = $timezone;
    }

    /**
     * Model initialization.
     */
    protected function _construct()
    {
        $this->_init('md_subscribenow_aggregated_customer', 'id');
    }

    /**
     * Aggregate subscription customer by subscription created at.
     *
     * @param string|int|DateTime|array|null $from
     * @param string|int|DateTime|array|null $to
     *
     * @return $this
     *
     * @throws Exception
     * @SuppressWarnings(PHPMagedelight.ExcessiveMethodLength)
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_aggregateBySubscriptionCustomer($from, $to);
        $this->_setFlagData(Flag::REPORT_CUSTOMER_SUBSCRIPTION_FLAG_CODE);

        return $this;
    }

    /**
     * Aggregate subscription customer by create_at as period.
     *
     * @param string|null $from
     * @param string|null $to
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function _aggregateBySubscriptionCustomer($from, $to)
    {
        $table = $this->getTable('md_subscribenow_aggregated_customer');
        $sourceTable = $this->getTable('md_subscribenow_product_subscribers');
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect($sourceTable, 'created_at', 'updated_at', $from, $to);
            } else {
                $subSelect = null;
            }
            $this->_clearTableByDateRange($table, $from, $to, $subSelect);

            // convert dates to current admin timezone
            $periodExpr = $connection->getDatePartSql(
                $this->getStoreTZOffsetQuery($sourceTable, $sourceTable . '.created_at', $from, $to)
            );

            $columns = [
                'period' => $periodExpr,
                'store_id' => 'store_id',
                'customer_id' => 'customer_id',
                'customer_name' => 'subscriber_name',
                'customer_email' => new \Zend_Db_Expr('MIN(subscriber_email)'),
                'subscriber_count' => new Zend_Db_Expr('COUNT(subscription_id)'),
                'active_subscriber' => new Zend_Db_Expr(
                    "SUM(case when subscription_status = '1' then 1 else 0 end)"
                ),
                'pause_subscriber' => new Zend_Db_Expr(
                    "SUM(case when subscription_status = '2' then 1 else 0 end)"
                ),
                'cancel_subscriber' => new Zend_Db_Expr(
                    "SUM(case when subscription_status = '4' then 1 else 0 end)"
                )
            ];
            $select = $connection->select();
            $select->from(
                $sourceTable,
                $columns
            );
            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }
            $select->group([$periodExpr, 'store_id', 'customer_id']);
            $select->having('subscriber_count > 0');

            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
            $select->reset();

            $columns = [
                'period' => 'period',
                'store_id' => new Zend_Db_Expr(Store::DEFAULT_STORE_ID),
                'customer_id' => 'customer_id',
                'customer_name' => new Zend_Db_Expr('MIN(customer_name)'),
                'customer_email' => new Zend_Db_Expr('MIN(customer_email)'),
                'subscriber_count' => new Zend_Db_Expr('COUNT(subscriber_count)'),
                'active_subscriber' => new Zend_Db_Expr('SUM(active_subscriber)'),
                'pause_subscriber' => new Zend_Db_Expr('SUM(pause_subscriber)'),
                'cancel_subscriber' => new Zend_Db_Expr('SUM(cancel_subscriber)')
            ];
            $select->from($table, $columns)->where('store_id != ?', Store::DEFAULT_STORE_ID);
            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }
            $select->group(['period', 'customer_id']);
            $insertQuery = $select->insertFromSelect($table, array_keys($columns));
            $connection->query($insertQuery);
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();

        return $this;
    }
}
