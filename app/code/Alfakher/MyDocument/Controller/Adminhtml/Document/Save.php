<?php

namespace Alfakher\MyDocument\Controller\Adminhtml\Document;

use Alfakher\MyDocument\Helper\Data;
use Alfakher\MyDocument\Model\MyDocument;
use Alfakher\MyDocument\Model\MyDocumentFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends \Magento\Backend\App\Action
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
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument
     * @param MyDocument $documentModel
     * @param Data $helper
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $adapterFactory
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument,
        MyDocument $documentModel,
        Data $helper,
        \Magento\Framework\Controller\ResultFactory $result,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->_myDocument = $myDocument;
        $this->documentModel = $documentModel;
        $this->resultRedirect = $result;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Execute MyDocument

     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $count = count($post['document_name']);
        $newArray = [];
        $data = [];
        $customerDocs = [];
        $emailData = [];

        if (isset($post['is_customerfrom_usa'])) {
            $is_usa = 1;
        } else {
            $is_usa = 0;
        }

        $filesData = $this->getRequest()->getFiles()->toArray();

        if (count($filesData)) {
            $i = 0;
            foreach ($filesData as $key => $files) {
                if (isset($files['tmp_name']) && strlen($files['tmp_name']) > 0) {
                    try {
                        $uploaderFactories = $this->uploaderFactory->create(['fileId' => $filesData[$key]]);
                        $uploaderFactories->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'pdf']);
                        $imageAdapter = $this->adapterFactory->create();
                        $uploaderFactories->addValidateCallback(
                            'custom_image_upload',
                            $uploaderFactories,
                            'validateUploadFile'
                        );

                        /*Allow folder creation*/
                        $uploaderFactories->setAllowCreateFolders(true);
                        $maxsize = 20;
                        /*number_format($_FILES['filename']['size'] / 1048576, 2) . ' MB';*/
                        if ((number_format($files['size'] / 1048576, 2) >= $maxsize)) {
                            throw new LocalizedException(
                                __('File too large. File must be less than 20 megabytes.')
                            );
                        }
                        /*Rename file name if already exists*/
                        $uploaderFactories->setAllowRenameFiles(true);
                        $uploaderFactories->setFilesDispersion(false);
                        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                        $destinationPath = $mediaDirectory->getAbsolutePath('myDocument');
                        $result = $uploaderFactories->save($destinationPath);
                        if (!$result) {
                            throw new LocalizedException(
                                __('File cannot be saved to path: $1', $destinationPath)
                            );
                        }
                        $imagePath = $result['file'];
                        $data['filename'] = $imagePath;
                        $newArray[] = $imagePath;

                    } catch (\Exception $e) {
                        $this->messageManager->addError(__($e->getMessage()));
                    }
                }
            }
        }

        $model = $this->_myDocument->create();
        $j = 0;

        for ($i = 0; $i < $count; $i++) {
            if (array_key_exists("document_name", $post)) {
                if (isset($post['mydocument_id'][$i])) {
                    $entity = $this->documentModel->load($post['mydocument_id'][$i]);
                    if ($entity) {
                        $entity->setMessage($post['message'][$i]);
                        $entity->setStatus(empty($post['message'][$i]) ? 1 : 0);
                        $entity->setDocumentName($post['document_name'][$i]);
                        $entity->setExpiryDate($this->convertDate($post['expiry_date'][$i]));
                    }

                    $emailData[] = ($entity->getData());
                    $entity->save();

                } else {
                    $model = $this->_myDocument->create();
                    if ($post['document_name'][$i] != '') {
                        $docName = $post['document_name'][$i];
                        if ($docName == "FEIN" ||
                            $docName == "Sales Tax/Resale License" ||
                            $docName == "State Tobacco License" ||
                            $docName == "Unified Resale Certificate") {
                            $is_add_more_form = 0;
                        } else {
                            $is_add_more_form = 1;
                        }

                        $model->addData([
                            "document_name" => $post['document_name'][$i],
                            "customer_id" => $post['customer_id'],
                            "expiry_date" => $this->convertDate($post['expiry_date'][$i]),
                            "status" => isset($post['message'][$i]) ? 1 : 0,
                            "message" => !empty($post['message'][$i]) ? $post['message'][$i] : '',
                            "is_customerfrom_usa" => $is_usa,
                            "is_add_more_form" => $is_add_more_form,
                            "filename" => $newArray[$j],
                        ]);
                        $model->setIsDelete(false);
                        $model->setStatus(0);
                        $j++;

                    }
                    if (($post['document_name'][$i]) != '') {
                        $saveData = $model->save();
                        $customerDocs[] = $saveData->getData();
                    }

                }

            }
        }
        $customer = $this->_customerFactory->create()->load($post['customer_id'])->getDataModel();
        if (count($emailData) > 0) {
            $customer->setCustomAttribute('uploaded_doc', 1);
            $this->_customerRepositoryInterface->save($customer);
            $this->_eventManager->dispatch('document_update_after', [
                'items' => $emailData,
            ]);
        }

        if (count($customerDocs) > 0) {
            $customer->setCustomAttribute('uploaded_doc', 1);
            $this->_customerRepositoryInterface->save($customer);
            $this->_eventManager->dispatch('document_save_after', [
                'items' => $customerDocs,
            ]);
        }
        $mail = $this->helper->sendMail($emailData, $post['customer_id']);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/index/edit', ['id' =>
            $post['customer_id'], '_current' => false, 'active_tab' => 3]);
        return $resultRedirect;
    }

    /**
     * @inheritDoc
     */
    private function convertDate($value)
    {
        if ($value != '') {
            $date = ltrim($value, 'Expiry Date:');
            return date("Y-m-d", strtotime($date));
        }
        return '';
    }
}
