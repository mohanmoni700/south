<?php

namespace Alfakher\MyDocument\Controller\Adminhtml\Customer;

use Alfakher\MyDocument\Model\MyDocumentFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Uploaddocument extends \Magento\Backend\App\Action
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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $adapterFactory
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument,
        \Magento\Framework\Controller\ResultFactory $result,
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
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute MyDocument

     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $newArray = [];
        $data = [];

        if (isset($post['is_customerfrom_usa'])) {
            $is_usa = 1;
        } else {
            $is_usa = 0;
        }

        if (isset($post['is_add_more_form'])) {
            foreach ($post['is_add_more_form'] as $key => $value) {
                if ($value != '') {
                    $newArray[$key]['is_add_more_form'] = $value;
                } else {
                    $newArray[$key]['is_add_more_form'] = '';
                }
            }
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
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__($e->getMessage()));
                    }
                }

                if (isset($newArray[$i]['expiry_date'])) {
                    $date = ltrim($newArray[$i]['expiry_date'], 'Expiry Date:');
                    $expiryDate = date("Y-m-d", strtotime($date));
                } else {
                    $expiryDate = '';
                }

                $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('mydocument/customer/index');
                $model = $this->_myDocument->create();
                $model->setData($data);

                $model->addData([
                    "document_name" => $post['name' . ($i + 1)],
                    "customer_id" => $post['customer_id'],
                    "expiry_date" => $this->convertDate($post['expiry_date' . ($i + 1)]),
                    "is_customerfrom_usa" => $is_usa,
                    "status" => 0,
                    "is_add_more_form" => $newArray[$i]['is_add_more_form'],
                ]);
                $model->setIsDelete(false);
                $model->setStatus(0);
                $saveData = $model->save();
                $i++;
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        if ($saveData) {
            $htmlContent = "Record Saved Successfully.";
            $success = true;
        } else {
            $htmlContent = '';
            $success = false;
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
