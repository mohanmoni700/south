<?php

namespace Alfakher\ExciseTax\Controller\Adminhtml\Report;

class Index extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    public function execute()
    {

        $this->_initAction()->_setActiveMenu(
            'Alfakher_ExciseTax::report_excisetax_order'
        )->_addBreadcrumb(
            __('Excise Order Report'),
            __('Excise Order Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Excise Order Report'));

        $this->_view->renderLayout();
    }
}
