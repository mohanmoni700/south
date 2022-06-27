<?php

namespace Alfakher\MyDocument\Controller\Customer;

use Alfakher\MyDocument\Model\MyDocumentFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Deletedocument extends Action
{

    /**
     * @var \Alfakher\MyDocument\Model\MyDocumentFactory
     */
    protected $_myDocument;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param \Magento\Customer\Model\Session $customerSession
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $adapterFactory
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Customer\Model\Session $customerSession,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_myDocument = $myDocument;
        $this->resultRedirect = $result;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $documentId = $this->getRequest()->getPost("id");
        $model = $this->_myDocument->create()->load($documentId);
        
        if ($model) {
            $model->setIsDelete(true);
            $model->save();
            $success = true;
            $this->_eventManager->dispatch('document_delete_after',
                [
                    'items' => $model->getData()
                ]
            );
        } else {
            $success = false;
        }
        return $resultJson->setData([
            'success' => $success]);
    }
}
