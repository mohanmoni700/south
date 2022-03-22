<?php
/**
 * Webkul MultiQuickbooksConnect
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Controller\Adminhtml;

abstract class AbstractAccount extends \Magento\Backend\App\Action
{
    /**
     * Check the permission to Manage Accounts
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MultiQuickbooksConnect::account_list');
    }
}
