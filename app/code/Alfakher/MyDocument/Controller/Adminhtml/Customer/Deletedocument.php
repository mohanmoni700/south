<?php

namespace Alfakher\MyDocument\Controller\Adminhtml\Customer;

use Alfakher\MyDocument\Model\MyDocumentFactory;
use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
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
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param \Magento\Customer\Model\Session $customerSession
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $adapterFactory
     * @param Filesystem $filesystem
     * @param CollectionFactory $collection
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Customer\Model\Session $customerSession,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        CollectionFactory $collection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->_myDocument = $myDocument;
        $this->resultRedirect = $result;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->collection = $collection;
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerFactory = $customerFactory;
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
        $customerId = $model->getData('customer_id');

        $documentCollection = $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $customerId]);

        $customer = $this->_customerFactory->create()->load($customerId)->getDataModel();
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

        } else {
            $success = false;
        }
        return $resultJson->setData([
            'success' => $success]);
    }
}
