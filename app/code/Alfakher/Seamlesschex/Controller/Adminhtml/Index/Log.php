<?php

namespace Alfakher\Seamlesschex\Controller\Adminhtml\Index;

class Log extends \Magento\Backend\App\Action
{
    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute method
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::stores_other_settings');
        $resultPage->addBreadcrumb(__('Seamlesschex(ACH)'), __('Seamlesschex(ACH)'));
        $resultPage->getConfig()->getTitle()->prepend(__('Seamlesschex(ACH) Logs'));

        return $resultPage;
    }

    /**
     * Is Allowed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Alfakher_Seamlesschex::seamlesschex_log');
    }
}
