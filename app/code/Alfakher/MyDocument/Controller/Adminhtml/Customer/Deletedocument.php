<?php

namespace Alfakher\MyDocument\Controller\Adminhtml\Customer;

use Alfakher\MyDocument\Model\MyDocumentFactory;
use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Deletedocument extends \Magento\Backend\App\Action
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
     * @param MyDocumentFactory $myDocument
     * @param CollectionFactory $collection
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerFactory $customerFactory
     * @param Session $customerSession
     * @param ResultFactory $result
     * @param JsonFactory $resultJsonFactory
     * @param Filesystem $filesystem
     * @param AdapterFactory $adapterFactory
     * @param UploaderFactory $uploaderFactory
     */

    public function __construct(
        MyDocumentFactory $myDocument,
        CollectionFactory $collection,
        Context $context,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory,
        Session $customerSession,
        ResultFactory $result,
        JsonFactory $resultJsonFactory,
        Filesystem $filesystem,
        AdapterFactory $adapterFactory,
        UploaderFactory $uploaderFactory
    ) {
        parent::__construct($context);
        $this->_myDocument = $myDocument;
        $this->collection = $collection;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->resultRedirect = $result;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filesystem = $filesystem;
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $documentId = $this->getRequest()->getPost("id");
        $model = $this->_myDocument->create()->load($documentId);
        $itemData = $model->getData();
        $customerId = $itemData['customer_id'];

        $documentCollection = $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $customerId]);

        $customer = $this->_customerFactory->create()->load($customerId)->getDataModel();
        $success = false;

        if ($model) {
            $model->delete();
            $success = true;
            $this->_eventManager->dispatch('document_delete_after', [
                'items' => $itemData,
            ]);

            if (!empty($documentCollection->getData())) {
                $customer->setCustomAttribute('uploaded_doc', 1);
                $this->_customerRepositoryInterface->save($customer);
            } else {
                $customer->setCustomAttribute('uploaded_doc', 0);
                $this->_customerRepositoryInterface->save($customer);
            }

        }
        return $resultJson->setData([
            'success' => $success]);
    }
}
