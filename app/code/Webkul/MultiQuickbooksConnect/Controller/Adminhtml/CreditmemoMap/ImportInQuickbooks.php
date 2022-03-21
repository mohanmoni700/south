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
namespace Webkul\MultiQuickbooksConnect\Controller\Adminhtml\CreditmemoMap;

use Magento\Backend\App\Action;

class ImportInQuickbooks extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MultiQuickbooksConnect::manager');
        $resultPage->getConfig()->getTitle()->prepend(__('Exporting Credit Memos are in Process'));
        return $resultPage;
    }

    /**
     * Check Creditmemo Profiler Permission Check.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MultiQuickbooksConnect::creditmemo_import');
    }
}
