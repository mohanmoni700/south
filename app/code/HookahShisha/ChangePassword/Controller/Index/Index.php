<?php
namespace HookahShisha\ChangePassword\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->_customerSession = $session;
        return parent::__construct($context);
    }

    public function execute()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_pageFactory->create();
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
    }
}
