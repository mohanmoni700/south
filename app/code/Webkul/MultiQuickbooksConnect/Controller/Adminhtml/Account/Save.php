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

class Save extends QuickbooksAccount
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
            $data = $this->getRequest()->getParams();
            if ($data['id'] == 'active_tab') {
                unset($data['id']);
            }
            $accountId = $data['id'] ?? 0;

            // For checking store Id
            $storeId = $data['store_id'] ?? 0;
            $quickbooksAccountCollection = $this->quickbooksAccount->create()->getCollection()
                ->addFieldToFilter('store_id', $storeId);
            if ($quickbooksAccountCollection->getSize()) {
                $quickbooksAccountId = $quickbooksAccountCollection->getFirstItem()->getId();
                if ($accountId != $quickbooksAccountId) {
                    $message = __('Another account with same store view already exists!');
                    $this->messageManager->addError($message);
                    $this->_redirect('*/*/');
                    return;
                }
            }
            // For checking account name
            $accountName = $data['account_name'] ?? 0;
            $quickbooksAccountCollection = $this->quickbooksAccount->create()->getCollection()
                ->addFieldToFilter('account_name', $accountName);
            if ($quickbooksAccountCollection->getSize()) {
                $quickbooksAccountId = $quickbooksAccountCollection->getFirstItem()->getId();
                if ($accountId != $quickbooksAccountId) {
                    $message = __('Another account with same name already exists!');
                    $this->messageManager->addError($message);
                    $this->_redirect('*/*/');
                    return;
                }
            }

            $quickbooksAccountModel = $this->quickbooksAccount->create();
            if ($accountId) {
                unset($data['store_id']);
                unset($data['created_at']);
                $quickbooksAccountModel->addData($data)->setId($accountId)->save();
                $this->messageManager->addSuccess(__('Account saved successfully.'));
            } else {
                $quickbooksAccountModel->setData($data)->save();
                $this->messageManager->addSuccess(__('Account saved successfully. You need to authorize it'));
            }
            $this->_redirect('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
    }
}
