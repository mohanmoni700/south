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
namespace Webkul\MultiQuickbooksConnect\Controller\Adminhtml\Account;

use Magento\Framework\Controller\ResultFactory;
use Webkul\MultiQuickbooksConnect\Controller\Adminhtml\AbstractAccount as QuickbooksAccount;
use Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit as AccountEdit;
use Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit\Tabs;

class Edit extends QuickbooksAccount
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Model\AccountFactory
     */
    private $quickbooksAccount;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\MultiQuickbooksConnect\Model\AccountFactory $quickbooksAccount
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\MultiQuickbooksConnect\Model\AccountFactory $quickbooksAccount
    ) {
        $this->backendSession = $context->getSession();
        $this->registry = $registry;
        $this->quickbooksAccount = $quickbooksAccount;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $quickbooksAccountModel = $this->quickbooksAccount->create();
            if (isset($params['id']) && $params['id']) {
                $collection = $this->quickbooksAccount->create()->getCollection();
                foreach ($collection as $model) {
                    if ($model->getId() == $params['id']) {
                        $this->setCurrentStatus($model, 1);
                    } else {
                        $this->setCurrentStatus($model, 0);
                    }
                }
                $quickbooksAccountModel->load($params['id']);
            }
            $data = $this->backendSession->getFormData(true);
            if (!empty($data)) {
                $quickbooksAccountModel->setData($data);
            }

            $this->registry->register('multi_quickbooksaccount_info', $quickbooksAccountModel);

            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->setActiveMenu('Webkul_MultiQuickbooksConnect::manager');
            $resultPage->getConfig()->getTitle()->prepend(__('QB Account'));
            $resultPage->getConfig()->getTitle()->prepend(
                $quickbooksAccountModel->getId() ? $quickbooksAccountModel->getAccountName() : __('QB Account')
            );
            $resultPage->addContent($resultPage->getLayout()->createBlock(AccountEdit::class));
            $resultPage->addLeft($resultPage->getLayout()->createBlock(Tabs::class));
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
    }

    /**
     * Set the 'is_current' to '1' for the current account (to be used in authentication)
     *
     * @param object $model
     * @param int $flag
     * @return void
     */
    private function setCurrentStatus($model, $flag)
    {
        $model->setIsCurrent($flag)->save();
    }
}
