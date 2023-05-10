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


namespace Magedelight\Subscribenow\Controller\Adminhtml\Report\Product;

use Magedelight\Subscribenow\Model\Flag;

class View extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{

    /**
     * Check is allowed for report.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Subscribenow::subscribenow_product');
    }

    /**
     * Product base subscription report.
     */
    public function execute()
    {
        try {
            $this->_showLastExecutionTime(Flag::REPORT_PRODUCT_SUBSCRIPTION_FLAG_CODE, 'product_subscription');

            $this->_initAction()->_setActiveMenu(
                'Magedelight_Base::md_base_root'
            )->_addBreadcrumb(
                __('Product Subscription Report'),
                __('Product Subscription Report')
            );
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Product Wise Subscriptions Report'));

            $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_report_product_view.grid');
            $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

            $this->_initReportAction([$gridBlock, $filterFormBlock]);

            $this->_view->renderLayout();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('An error occurred while showing the product subscription report. Please review the log and try again.')
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_redirect('subscribenow/*/view/');

            return;
        }
    }
}
