<?php
namespace Alfakher\MyDocument\Controller\Adminhtml\Document;

use Alfakher\MyDocument\Helper\Data;
use Alfakher\MyDocument\Model\MyDocument;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Saveform extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultRedirectFactory;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultPageFactory;

    /**
     * @var MyDocument
     */
    protected $documentModel;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param MyDocument $documentModel
     * @param Data $helper
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $adapterFactory
     * @param Filesystem $filesystem
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        MyDocument $documentModel,
        Data $helper,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        array $data = []
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->documentModel = $documentModel;
        $this->helper = $helper;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * Execute MyDocument

     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $post = (array) $this->getRequest()->getPost();
        $customerid = $this->getRequest()->getParam('customer_id');
        $customerDocs = [];
        try {

            $newArray = [];
            foreach ($post['mydocument_id'] as $key => $value) {

                $newArray[$key]['mydocument_id'] = $post['mydocument_id'][$key];
                $newArray[$key]['status'] = empty($post['message'][$key]) ? 1 : 0;
                $newArray[$key]['document_name'] = $post['document_name'][$key];
                $newArray[$key]['message'] = $post['message'][$key];
                $newArray[$key]['expiry_date'] = $post['expiry_date'][$key];
                $document_id = $post['mydocument_id'][$key];

                $filesData = $this->getRequest()->getFiles()->toArray();
                if (count($filesData) > 0) {
                    $files = isset($filesData['updatefile' . $document_id]) ? $filesData['updatefile' . $document_id] : '';
                    if ($files && isset($files['tmp_name']) && strlen($files['tmp_name']) > 0) {
                        $uploaderFactories = $this->uploaderFactory->create(['fileId' => $files]);
                        $uploaderFactories->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'pdf']);
                        $imageAdapter = $this->adapterFactory->create();
                        $uploaderFactories->addValidateCallback(
                            'custom_image_upload',
                            $uploaderFactories,
                            'validateUploadFile'
                        );
                        $uploaderFactories->setAllowCreateFolders(true);
                        $maxsize = 20;
                        $uploaderFactories->setAllowRenameFiles(true);
                        $uploaderFactories->setFilesDispersion(false);
                        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                        $destinationPath = $mediaDirectory->getAbsolutePath('myDocument');
                        $result = $uploaderFactories->save($destinationPath);

                        $imagePath = $result['file'];
                        $newArray[$key]['filename'] = $imagePath;
                    }
                }
            }
            foreach ($newArray as $key => $value) {
                $entity = $this->documentModel->load($value['mydocument_id']);
                if ($entity) {
                    $entity->setStatus($value['status']);
                    $entity->setMessage($value['message']);
                    $entity->setDocumentName($value['document_name']);
                    $entity->setExpiryDate($value['expiry_date']);
                    if (isset($value['filename']) && $value['filename']) {
                        $entity->setFilename($value['filename']);
                        $entity->setIsDelete(0);
                    }
                    $entity->save();
                    $customerDocs[] =  $entity->getData();
                }
            }
            $this->_eventManager->dispatch('document_update_after',
                [
                    'items' => $customerDocs
                ]
            );
            $this->messageManager->addSuccess(__('The data has been saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("Something went wrong."));
        }
        $mail = $this->helper->sendMail($newArray, $customerid);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/index/edit', ['id' => $customerid, '_current' => false, 'active_tab' => 3]);
        return $resultRedirect;
    }
}
