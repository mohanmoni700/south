<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Controller\Prequalification;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action\Action
{
    /**
     * Result Page
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Action\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $session;
        parent::__construct($context);
    }

    /**
     * Execute method
     *
     * @return PageFactory
     */
    public function execute()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Slope Pre-Qualification'));
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login/');
            return $resultRedirect;
        }
    }
}
