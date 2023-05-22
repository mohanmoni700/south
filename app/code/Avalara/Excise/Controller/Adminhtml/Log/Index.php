<?php

namespace Avalara\Excise\Controller\Adminhtml\Log;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
/**
 * @codeCoverageIgnore
 */
class Index extends LogAbstract
{
    /**
     * Log page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var Page $pageResult */
        $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $pageResult->setActiveMenu('Avalara_Excise::avatax_log');
        $pageResult->getConfig()->getTitle()->prepend(__('AvaTax Excise Logs'));
        return $pageResult;
    }
}
