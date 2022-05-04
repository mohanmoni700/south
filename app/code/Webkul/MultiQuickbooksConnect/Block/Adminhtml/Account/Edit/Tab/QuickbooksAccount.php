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
use Webkul\MultiQuickbooksConnect\Model\Source;

class QuickbooksAccount extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\QuickBooks
     */
    private $quickBooksHelper;

    /**
     * @var Source\Store
     */
    private $storeOptions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Webkul\MultiQuickbooksConnect\Helper\QuickBooks $quickBooksHelper
     * @param Source\Store $storeOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Webkul\MultiQuickbooksConnect\Helper\QuickBooks $quickBooksHelper,
        Source\Store $storeOptions,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->quickBooksHelper = $quickBooksHelper;
        $this->storeOptions = $storeOptions;
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
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('QB Account'), 'class' => 'fieldset-wide']
        );
        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'id']);
        }
        
        if (!$model->getId()) {
            $fieldset->addField(
                'account_name',
                'text',
                [
                    'name' => 'account_name',
                    'label' => __('Account Name'),
                    'title' => __('Account Name'),
                    'required' => true,
                    'note' => __('Set the unique name for account alongwith Qb company. You cannot change it later.')
                ]
            );
            $fieldset->addField(
                'store_id',
                'select',
                [
                    'name' => 'store_id',
                    'label' => __('Associate to Store'),
                    'title' => __('Associate to Store'),
                    'required' => true,
                    'options' => $this->storeOptions->toArray(),
                    'note' => __('Select the Store to associate with the account.')
                ]
            );
        } else {
            $fieldset->addField(
                'account_name',
                'text',
                [
                    'name' => 'account_name',
                    'label' => __('Account Name'),
                    'title' => __('Account Name'),
                    'required' => true,
                    'readonly' => true
                ]
            );
            $titleHeading = __("My Connect Page");
            $tockenStatus = $this->isRefreshTockenExpired($model->getId()) ? 'expired' : 'notexpired';
            $fieldset->addField(
                'store_id',
                'select',
                [
                    'name' => 'store_id',
                    'label' => __('Associate to Store'),
                    'title' => __('Associate to Store'),
                    'required' => true,
                    'options' => $this->storeOptions->toArray(),
                    'disabled' =>true
                ]
            )->setAfterElementHtml('
            <div class="pp-buttons-container '.$tockenStatus.'">
            <ipp:connectToIntuit></ipp:connectToIntuit>
            <p class="mark '.$tockenStatus.'"</p>
            </div>
            <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"></meta>
            <title>'.$titleHeading.'</title>
            <script type="text/javascript" src="https://appcenter.intuit.com/Content/IA/intuit.ipp.anywhere.js">
            </script>
            <script>
            // Runnable uses dynamic URLs so we need to detect our current //
            // URL to set the grantUrl value   ########################### //
            /*######*/ var parser = document.createElement("a");/*#########*/
            /*######*/parser.href = document.url;/*########################*/
            // end runnable specific code snipit ##########################//
            intuit.ipp.anywhere.setup({
                menuProxy: "",
                grantUrl: "'.$this->getGrantUrl().'"
                // outside runnable you can point directly to the oauth.php page
            });
            </script>
            ');
        }
        
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * isRefreshTockenExpired
     */
    public function isRefreshTockenExpired($accountId)
    {
        return $this->quickBooksHelper->getAccounts($accountId) ? false : true;
    }

    /**
     * Return ajax url for button.
     * @return string
     */
    public function getGrantUrl()
    {
        $integratesWith = $this->_scopeConfig->getValue(
            'wk_multi_quickbooks_connect/general_settings/app_integrates_with'
        );
        $path = ($integratesWith == 'oauth2') ? 'multiquickbooksconnect/oauth/oauth2' : '';
        return $this->getBaseUrl().$path;
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
