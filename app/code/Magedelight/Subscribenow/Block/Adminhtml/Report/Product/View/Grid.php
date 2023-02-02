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

namespace Magedelight\Subscribenow\Block\Adminhtml\Report\Product\View;

class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{

    protected $_columnGroupBy = 'period';

    /**
     * Grid resource collection name.
     *
     * @var string
     */
    protected $_resourceCollectionName = 'Magedelight\Subscribenow\Model\ResourceModel\Report\Subscription\Product\Collection';

    /**
     * Init grid parameters.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    /**
     * Custom columns preparation.
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'period',
            [
            'header' => __('Interval'),
            'index' => 'period',
            'sortable' => false,
            'period_type' => $this->getPeriodType(),
            'renderer' => 'Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date',
            'totals_label' => __('Total'),
            'subtotals_label' => __('Subtotal'),
            'html_decorators' => ['nobr'],
            'header_css_class' => 'col-period',
            'column_css_class' => 'col-period',
                ]
        );

        $this->addColumn(
            'product_name',
            [
            'header' => __('Product Name'),
            'index' => 'product_name',
            'type' => 'string',
            'sortable' => false,
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name',
                ]
        );

        $this->addColumn(
            'subscriber_count',
            [
            'header' => __('Subscriber Count'),
            'index' => 'subscriber_count',
            'total' => 'sum',
            'type' => 'number',
            'sortable' => false,
            'header_css_class' => 'col-qty',
            'column_css_class' => 'col-qty',
                ]
        );

        $this->addColumn(
            'active_subscriber',
            [
            'header' => __('Active Subscriber'),
            'index' => 'active_subscriber',
            'total' => 'sum',
            'type' => 'number',
            'sortable' => false,
            'header_css_class' => 'col-qty',
            'column_css_class' => 'col-qty',
                ]
        );

        $this->addColumn(
            'pause_subscriber',
            [
            'header' => __('Pause Subscriber'),
            'index' => 'pause_subscriber',
            'total' => 'sum',
            'type' => 'number',
            'sortable' => false,
            'header_css_class' => 'col-qty',
            'column_css_class' => 'col-qty',
                ]
        );
        $this->addColumn(
            'cancel_subscriber',
            [
            'header' => __('Cancel Subscriber'),
            'index' => 'cancel_subscriber',
            'total' => 'sum',
            'type' => 'number',
            'sortable' => false,
            'header_css_class' => 'col-qty',
            'column_css_class' => 'col-qty',
                ]
        );

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        $this->addExportType('*/*/exportSubscriptionProductCsv', __('CSV'));
        $this->addExportType('*/*/exportSubscriptionProductExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
