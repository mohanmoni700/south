<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category  Magedelight
 * @package   Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Plugin\Magento\Reports\Model\ResourceModel\Refresh;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Reports\Model\FlagFactory
     */
    protected $_reportsFlagFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory
    ) {
        parent::__construct($entityFactory);
        $this->_localeDate = $localeDate;
        $this->_reportsFlagFactory = $reportsFlagFactory;
    }

    /**
     * Get if updated
     *
     * @param string $reportCode
     * @return string
     */
    protected function _getUpdatedAt($reportCode)
    {
        $flag = $this->_reportsFlagFactory->create()->setReportFlagCode($reportCode)->loadSelf();
        return $flag->hasData() ? $flag->getLastUpdate() : '';
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoadData($subject, $result, $printQuery = false, $logQuery = false)
    {
        if (!count($this->_items)) {
            $data = [
                [
                    'id' => 'product_subscription',
                    'report' => __('Product Subscription'),
                    'comment' => __('Total Product Subscription Report'),
                    'updated_at' => $this->_getUpdatedAt(\Magedelight\Subscribenow\Model\Flag::REPORT_PRODUCT_SUBSCRIPTION_FLAG_CODE),
                ],
                [
                    'id' => 'customer_subscription',
                    'report' => __('Customer Subscription'),
                    'comment' => __('Total Customer Subscription Report'),
                    'updated_at' => $this->_getUpdatedAt(\Magedelight\Subscribenow\Model\Flag::REPORT_CUSTOMER_SUBSCRIPTION_FLAG_CODE),
                ]
            ];
            foreach ($data as $value) {
                $item = new \Magento\Framework\DataObject();
                $item->setData($value);
                $this->addItem($item);
                $subject->addItem($item);
            }
        }
        return $subject;
    }
}
