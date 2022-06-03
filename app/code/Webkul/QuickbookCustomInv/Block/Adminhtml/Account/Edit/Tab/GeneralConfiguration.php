<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_QuickbookCustomInv
 * @author      Webkul
 * @copyright   Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\QuickbookCustomInv\Block\Adminhtml\Account\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Webkul\MultiQuickbooksConnect\Model\Config\Source;

class GeneralConfiguration extends \Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit\Tab\GeneralConfiguration
{
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('multi_quickbooksaccount_info');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('QB General Configuration')]);

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }
        $fieldset->addField(
            'sales_receipt_create_on',
            'select',
            [
                'name' => 'sales_receipt_create_on',
                'label' => __('Sales Receipt Create On Quickbook'),
                'title' => __('Sales Receipt Create On Quickbook'),
                'required' => true,
                'options' => $this->salesReceiptCreateOn->toArray(),
                'note' => __('Select the event for exporting orders automatically.')
            ]
        );
        $fieldset->addField(
            'creditmemo_auto_sync',
            'select',
            [
                'name' => 'creditmemo_auto_sync',
                'label' => __('Creditmemo Auto Sync to Quickbooks'),
                'title' => __('Creditmemo Auto Sync to Quickbooks'),
                'required' => true,
                'options' => $this->getStatusOptions(),
                'note' => __('Select to enable or disable the auto exporting.')
            ]
        );
        $fieldset->addField(
            'us_store',
            'select',
            [
                'name' => 'us_store',
                'label' => __('QuickBooks US Store'),
                'title' => __('QuickBooks US Store'),
                'required' => true,
                'options' => $this->getStatusOptions(),
                'note' => __('Identify that your Quickbooks account is USA or Non-USA.')
            ]
        );
        $fieldset->addField(
            'asset_account',
            'select',
            [
                'name' => 'asset_account',
                'label' => __('Inventory Other Asset Account'),
                'title' => __('Inventory Other Asset Account'),
                'required' => true,
                'options' => $this->assetAccount->toArray($model->getId()),
                'note' => __('Choose any Inventory asset account.')
            ]
        );
        $fieldset->addField(
            'income_account',
            'select',
            [
                'name' => 'income_account',
                'label' => __('Income Account'),
                'title' => __('Income Account'),
                'required' => true,
                'options' => $this->incomeAccount->toArray($model->getId()),
                'note' => __('Choose any Income account.')
            ]
        );
        $fieldset->addField(
            'expense_account',
            'select',
            [
                'name' => 'expense_account',
                'label' => __('Expense Account'),
                'title' => __('Expense Account'),
                'required' => true,
                'options' => $this->expenseAccount->toArray($model->getId()),
                'note' => __('Choose any Expense account.')
            ]
        );
        $fieldset->addField(
            'default_tax_class',
            'text',
            [
                'name' => 'default_tax_class',
                'label' => __('Default Tax Class'),
                'title' => __('Default Tax Class'),
                'required' => true,
                'note' => __('Fill tax class wich set on exported orders.')
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return $this;
        //parent::_prepareForm();
    }
}
