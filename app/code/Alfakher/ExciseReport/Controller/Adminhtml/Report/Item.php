<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Report;

class Item extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{

    /**
     * Execute
     */
    public function execute()
    {

        $this->_initAction()->_setActiveMenu(
            'Alfakher_ExciseReport::report_excisetax_item'
        )->_addBreadcrumb(
            __('Excise Item Report'),
            __('Excise Item Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Excise Item Report'));

        $this->_view->renderLayout();
    }
}
