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

namespace Magedelight\Subscribenow\Block\Adminhtml\Report\Product;

class View extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * @var string
     */
    protected $_template = 'report/grid/container.phtml';

    /**
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magedelight_Subscribenow';
        $this->_controller = 'adminhtml_report_product_view';
        $this->_headerText = __('Product Subscription Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }

    /**
     * Get filter url.
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);

        return $this->getUrl('*/*/view', ['_current' => true]);
    }
}
