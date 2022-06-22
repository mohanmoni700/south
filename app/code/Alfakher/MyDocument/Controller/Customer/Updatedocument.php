<?php

namespace Alfakher\MyDocument\Controller\Customer;

use Alfakher\MyDocument\Model\MyDocumentFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\DataObject;

class Updatedocument extends Action
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
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     *
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
     * Execute MyDocument

     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $post = $this->getRequest()->getPostValue();
        $filesData = $this->getRequest()->getFiles()->toArray();
        $newArray = [];
        $data = [];
        $customerDocs = [];
        foreach ($post['documentid'] as $key => $value) {
            $newArray[$key]['documentid'] = $value;
        }
        if (count($filesData)) {
            $i = 0;
            foreach ($filesData as $files) {
                if (isset($files['tmp_name']) && strlen($files['tmp_name']) > 0) {

                    try {
                        $uploaderFactories = $this->uploaderFactory
                            ->create(['fileId' => $filesData['updatefile' . $newArray[$i]['documentid']]]);
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
                        $data['updatefile'] = $imagePath;
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__($e->getMessage()));
                    }

                } else {
                    $data['updatefile'] = null;
                }

                if (isset($post['expiry_date' . ($post['documentid'][$i])]) &&
                    $post['expiry_date' . ($post['documentid'][$i])] != "") {
                    $date = ltrim($post['expiry_date' . ($post['documentid'][$i])], 'Expiry Date:');
                    $expiryDate = date("Y-m-d", strtotime($date));
                } else {
                    $expiryDate = "";
                }

                $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('mydocument/customer/index');
                $model = $this->_myDocument->create()->load($newArray[$i]['documentid']);
                if ($data['updatefile'] != null) {
                    $model->setFileName($data['updatefile']);
                    $model->setDocumentName($post['name' . ($post['documentid'][$i])]);
                    $model->setExpiryDate($expiryDate);
                    $model->setIsDelete(false);
                    $model->setStatus(0);
                    $model->setMessage('');
                    $saveData = $model->save();
                    $customerDocs[] =  $saveData->getData();
                }
                $i++;
            }
        }
        
        if ($saveData) {
            $this->_eventManager->dispatch('document_update_after',
                [
                    'items' => $customerDocs
                ]
            );
            $success = true;
        } else {
            $success = false;
        }
        return $resultJson->setData([
            'success' => $success]);
    }
}
