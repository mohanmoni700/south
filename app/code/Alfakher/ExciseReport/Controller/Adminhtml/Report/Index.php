<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Report;

class Index extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{

    /**
     * Execute
     */
    public function execute()
    {

        $this->_initAction()->_setActiveMenu(
            'Alfakher_ExciseReport::report_excisetax_order'
        )->_addBreadcrumb(
            __('Excise Order Report'),
            __('Excise Order Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Excise Order Report'));

        $this->_view->renderLayout();
    }
}
