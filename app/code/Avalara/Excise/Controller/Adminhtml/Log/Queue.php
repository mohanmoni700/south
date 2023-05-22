<?php

namespace Avalara\Excise\Controller\Adminhtml\Log;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
/**
 * @codeCoverageIgnore
 */
class Queue extends LogAbstract
{
    /**
     * QueueLog page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var Page $pageResult */
        $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $pageResult->setActiveMenu('Avalara_Excise::excise_queue');
        $pageResult->getConfig()->getTitle()->prepend(__('AvaTax Excise Queue'));
        return $pageResult;
    }
}
