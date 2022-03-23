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
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit;

use Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quickbooks_account_tab');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Quickbooks Account Information'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $model = $this->coreRegistry->registry('multi_quickbooksaccount_info');
        $isAuthenticated = $model->getIsAuthenticated() ?? 0;
        $id = $this->getRequest()->getParam('id');
        $block = \Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit\Tab\QuickbooksAccount::class;
        $this->addTab(
            'quickbooks_account',
            [
                'label' => __('Connect QB Account'),
                'content' => $this->getLayout()->createBlock($block, 'quickbooks_account_info')->toHtml()
            ]
        );
        if ($id && $isAuthenticated) {
            $this->addTab(
                'general_configuration',
                [
                    'label' => __('General Configuration'),
                    'title' => __('General Configuration'),
                    'content' => $this->getLayout()->createBlock(Account\Edit\Tab\GeneralConfiguration::class)
                                                    ->toHtml(),
                    'active' => false
                ]
            );
            $this->addTab(
                'maporder',
                [
                    'label' => __('Mapped Order'),
                    'url'       => $this->getUrl('*/ordermap/index', ['_current' => true]),
                    'class'     => 'ajax',
                    'title'     => __('Mapped Order'),
                ]
            );
            $this->addTab(
                'exportorder',
                [
                    'label' => __('Export Order'),
                    'url'       => $this->getUrl('*/ordermap/export', ['_current' => true]),
                    'class'     => 'ajax',
                    'title'     => __('Export Order'),
                ]
            );
            $this->addTab(
                'mapcreditmemo',
                [
                    'label' => __('Mapped Credit Memo'),
                    'url'       => $this->getUrl('*/creditmemomap/index', ['_current' => true]),
                    'class'     => 'ajax',
                    'title'     => __('Mapped Credit Memo'),
                ]
            );
            $this->addTab(
                'exportcreditmemo',
                [
                    'label' => __('Export Credit Memo'),
                    'url'       => $this->getUrl('*/creditmemomap/export', ['_current' => true]),
                    'class'     => 'ajax',
                    'title'     => __('Export Credit Memo'),
                ]
            );
        }
        return parent::_prepareLayout();
    }
}
