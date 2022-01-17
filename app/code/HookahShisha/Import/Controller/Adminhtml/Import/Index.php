<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace HookahShisha\Import\Controller\Adminhtml\Import;

use Magento\Backend\Model\Auth\Session;

/**
 * Index Controller
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var _pageFactory
     */
    protected $_pageFactory;

    /**
     * @var backendSession
     */
    private $backendSession;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Session $backendSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        Session $backendSession
    ) {
        $this->_pageFactory = $pageFactory;
        $this->backendSession = $backendSession;
        return parent::__construct($context);
    }

    /**
     * Init execute
     */
    public function execute()
    {
        if ($this->isAdminLogin()) {
            $resultPage = $this->_pageFactory->create();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import'));
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('admin/index');
        }
    }

    /**
     * IsAdminLogin
     */
    public function isAdminLogin(): bool
    {
        return $this->backendSession->getUser() && $this->backendSession->getUser()->getId();
    }
}
