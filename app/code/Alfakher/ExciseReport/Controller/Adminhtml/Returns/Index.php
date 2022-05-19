<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Returns;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var $resultPageFactory
     */
    protected $resultPageFactory = false;
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
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Returns Report')));

        return $resultPage;
    }
}
