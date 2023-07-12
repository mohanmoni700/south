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

namespace Magedelight\Subscribenow\Controller\Adminhtml\Report\Customer;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class exportSubscriptionCustomerExcel extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{

    /**
     * Export shipping report grid to Excel XML format.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'md_subscribenow_customer_subscription.xml';
        $grid = $this->_view->getLayout()->createBlock('Magedelight\Subscribenow\Block\Adminhtml\Report\Customer\View\Grid');
        $this->_initReportAction($grid);

        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName), DirectoryList::VAR_DIR);
    }
}
