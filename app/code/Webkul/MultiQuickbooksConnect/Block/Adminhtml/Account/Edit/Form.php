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
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quickbooks_account_form');
        $this->setTitle(__('Quickbooks Account Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('multi_quickbooksaccount_info');
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'enctype' => 'multipart/form-data',
                    'action' => $this->getData('action')."id/".$model->getId(),
                    'method' => 'post'
                ]
            ]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
