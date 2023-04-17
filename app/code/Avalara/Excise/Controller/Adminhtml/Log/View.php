<?php

namespace Avalara\Excise\Controller\Adminhtml\Log;

use Avalara\Excise\Model\LogFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;

/**
 * View log
 * @codeCoverageIgnore
 */
class View extends LogAbstract
{
    /**
     * @var \Avalara\Excise\Model\LogFactory
     */
    protected $logFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Avalara\Excise\Model\LogFactory $logFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        LogFactory $logFactory,
        Registry $coreRegistry
    ) {
        $this->logFactory = $logFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            /** @var \Avalara\Excise\Model\Log $model */
            $model = $this->initLog();
            if (!$model->getId()) {
                $this->messageManager->addError(__('Requested log no longer exists.'));

                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }

            $dateString = $model->getCreatedAt();

            /** @var Page $pageResult */
            $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $pageResult->setActiveMenu('Avalara_Excise::avatax_log');
            $pageResult->getConfig()->getTitle()->prepend($dateString);
            return $pageResult;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to read log data to display.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $redirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

    /**
     * Load system report from request
     *
     * @param string $idFieldName
     * @return \Avalara\Excise\Model\Log $model
     */
    protected function initLog($idFieldName = 'id')
    {
        $id = (int)$this->getRequest()->getParam($idFieldName);

        /** @var \Avalara\Excise\Model\Log $model */
        $model = $this->logFactory->create();
        if ($id) {
            $model->load($id);
        }
        if (!$this->coreRegistry->registry('current_log')) {
            $this->coreRegistry->register('current_log', $model);
        }
        return $model;
    }
}
