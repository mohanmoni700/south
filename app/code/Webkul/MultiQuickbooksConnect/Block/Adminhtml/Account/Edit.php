<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialize MultiQuickbooksConnect account edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Webkul_MultiQuickbooksConnect';
        $this->_controller = 'adminhtml_account';
        parent::_construct();
        if ($this->_isAllowedAction('Webkul_MultiQuickbooksConnect::account_list')) {
            $this->buttonList->update('save', 'label', __('Save QB Account'));
        } else {
            $this->buttonList->remove('save');
        }
        $this->buttonList->remove('reset');
    }

    /**
     * Retrieve text for header element depending on loaded Group
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('multi_quickbooksaccount_info')->getId()) {
            $accountName = $this->_coreRegistry->registry('multi_quickbooksaccount_info')->getAccountName();
            $accountName = $this->escapeHtml($accountName);
            $heading = __("Edit QB Account")." ".$accountName;
            return $heading;
        } else {
            return __('New QB Account');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
