<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MultiQuickbooksConnect
 * @author      Webkul
 * @copyright   Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Webkul\MultiQuickbooksConnect\Model\Config\Source;

class GeneralConfiguration extends Generic implements TabInterface
{
    /**
     * @var Source\SalesReceiptCreateOn
     */
    private $salesReceiptCreateOn;

    /**
     * @var Source\Accounts\Asset
     */
    private $assetAccount;

    /**
     * @var Source\Accounts\Income
     */
    private $incomeAccount;

    /**
     * @var Source\Accounts\Expense
     */
    private $expenseAccount;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Source\SalesReceiptCreateOn $salesReceiptCreateOn
     * @param Source\Accounts\Asset $assetAccount
     * @param Source\Accounts\Income $incomeAccount
     * @param Source\Accounts\Expense $expenseAccount
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Source\SalesReceiptCreateOn $salesReceiptCreateOn,
        Source\Accounts\Asset $assetAccount,
        Source\Accounts\Income $incomeAccount,
        Source\Accounts\Expense $expenseAccount,
        array $data = []
    ) {
        $this->salesReceiptCreateOn = $salesReceiptCreateOn;
        $this->assetAccount = $assetAccount;
        $this->incomeAccount = $incomeAccount;
        $this->expenseAccount = $expenseAccount;
        parent::__construct($context, $registry, $formFactory, $data);
    }

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
        // $fieldset->addField(
        //     'status',
        //     'select',
        //     [
        //         'name' => 'status',
        //         'label' => __('Status'),
        //         'title' => __('Status'),
        //         'required' => true,
        //         'options' => $this->getStatusOptions(),
        //         'note' => __(' ')
        //     ]
        // );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return [
            '1' => __("Enabled"),
            '0' => __("Disabled")
        ];
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('QB Account');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('QB Account');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
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
