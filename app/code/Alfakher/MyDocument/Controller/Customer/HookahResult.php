<?php

namespace Alfakher\MyDocument\Controller\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;

class HookahResult extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Alfakher\MyDocument\Model\MyDocumentFactory
     */
    protected $myDocument;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     * @param \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Alfakher\MyDocument\Model\MyDocumentFactory $myDocument,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->myDocument = $myDocument;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        $post = $this->getRequest()->getPostValue();
        $filesData = $this->getRequest()->getFiles()->toArray();

        $response = [];

        if (count($filesData)) {
            $counter = 1;
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
                        $uploaderFactories->setAllowCreateFolders(true);
                        $maxsize = 20;
                        if ((number_format($files['size'] / 1048576, 2) >= $maxsize)) {
                            throw new LocalizedException(
                                __('File too large. File must be less than 20 megabytes.')
                            );
                        }
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
                        $model = $this->myDocument->create();
                        $model->setFilename($imagePath);
                        $model->setDocumentName($this->getArrayVal($post, 'name', $counter));
                        if($this->getArrayVal($post, 'is_customerfrom_usa', $counter) == 0)
                        {
                            $model->setCustomerId($post['cust_id_non_usa']);
                        } else {
                            $model->setCustomerId($post['cust_id_usa']);
                        }
                        $model->setExpiryDate($this->convertDate($this->getArrayVal($post, 'expiry_date', $counter)));
                        $model->setIsCustomerfromUsa($this->getArrayVal($post, 'is_customerfrom_usa', $counter));
                        $model->setIsDelete(false);
                        $model->setStatus(0);
                        $model->setIsAddMoreForm($this->getArrayVal($post, 'is_add_more_form', $counter));
                        $saveData = $model->save();
                        $response['success'] = 1;
                        $response['message'] = 'Record Saved Successfully';
                        $counter++;
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__($e->getMessage()));
                        $response['success'] = 0;
                        $response['message'] = $e->getMessage();
                    }
                }
            }
        }

        return $resultJson->setData($response);
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

    /**
     * @inheritDoc
     */
    private function getArrayVal($post,$field,$counter)
    {
        if(isset($post[$field . $counter]))
        {
            return $post[$field . $counter];
        }
        return '';
    }
}