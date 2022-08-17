<?php

namespace Alfakher\MyDocument\Controller\Adminhtml\Customer;

use Alfakher\MyDocument\Model\MyDocumentFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Uploaddoc extends \Magento\Backend\App\Action
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
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param MyDocumentFactory $myDocument
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerFactory $customerFactory
     * @param ResultFactory $result
     * @param JsonFactory $resultJsonFactory
     * @param Filesystem $filesystem
     * @param AdapterFactory $adapterFactory
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        MyDocumentFactory $myDocument,
        Context $context,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory $customerFactory,
        ResultFactory $result,
        JsonFactory $resultJsonFactory,
        Filesystem $filesystem,
        AdapterFactory $adapterFactory,
        UploaderFactory $uploaderFactory
    ) {
        parent::__construct($context);
        $this->_myDocument = $myDocument;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerFactory = $customerFactory;
        $this->resultRedirect = $result;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filesystem = $filesystem;
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * Execute MyDocument

     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $docArray = [];
        $data = [];
        $customerDocs = [];
        $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png', 'pdf'];
        $saveData = false;

        $is_usa = (isset($post['is_customerfrom_usa'])) ? 1 : 0;

        if (isset($post['is_add_more_form'])) {
            foreach ($post['is_add_more_form'] as $key => $value) {
                if ($value != '') {
                    $docArray[$key]['is_add_more_form'] = $value;
                } else {
                    $docArray[$key]['is_add_more_form'] = '';
                }
            }
        }

        $filesData = $this->getRequest()->getFiles()->toArray();
        if (count($filesData) > 0) {
            $i = 0;
            foreach ($filesData as $key => $files) {
                if (isset($files['tmp_name']) && strlen($files['tmp_name']) > 0) {
                    try {
                        $uploaderFactories = $this->uploaderFactory->create(['fileId' => $filesData[$key]]);
                        $uploaderFactories->setAllowedExtensions($allowedExtensions);
                        $imageAdapter = $this->adapterFactory->create();
                        $uploaderFactories->addValidateCallback(
                            'custom_image_upload',
                            $uploaderFactories,
                            'validateUploadFile'
                        );

                        /*Allow folder creation*/
                        $uploaderFactories->setAllowCreateFolders(true);
                        $maxsize = 20;

                        if ((round($files['size'] / 1048576, 2) >= $maxsize)) {
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
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__($e->getMessage()));
                    }
                }
                $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('mydocument/customer/index');
                $model = $this->_myDocument->create();
                $model->setData($data);
                if (array_key_exists("name" . ($i + 1), $post)) {
                    if ($post['name' . ($i + 1)] != "") {
                        $model->addData([
                            "document_name" => $post['name' . ($i + 1)],
                            "customer_id" => $post['customer_id'],
                            "expiry_date" => $this->convertDate($post['expiry_date' . ($i + 1)]),
                            "is_customerfrom_usa" => $is_usa,
                            "status" => 0,
                            "is_add_more_form" => $docArray[$i]['is_add_more_form'],
                        ]);
                        $model->setIsDelete(false);
                        $model->setStatus(0);
                        $saveData = $model->save();
                        $customerDocs[] = $saveData->getData();
                    }
                }
                $i++;
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $htmlContent = '';
        $success = false;
        if ($saveData) {
            $customer = $this->_customerFactory->create()->load($post['customer_id'])->getDataModel();
            $customer->setCustomAttribute('uploaded_doc', 1);
            $this->_customerRepositoryInterface->save($customer);
            $this->_eventManager->dispatch('document_save_after', [
                'items' => $customerDocs,
            ]);
            $htmlContent = "Record Saved Successfully.";
            $success = true;
        }
        return $resultJson->setData([
            'html' => $htmlContent,
            'tab' => 3,
            'success' => $success]);
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
