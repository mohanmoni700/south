<?php
namespace HookahShisha\Import\Controller\Adminhtml\Import;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Upload Controller
 */
class Upload extends \Magento\Framework\App\Action\Action
{
    /**
     * @var helper
     */
    protected $helper;

    /**
     * @var _csv
     */
    protected $_csv;

    /**
     * @var messageManager
     */
    protected $messageManager;

    /**
     * @var _resource
     */
    protected $_resource;

    /**
     * @var customer
     */
    protected $customer;

    /**
     * @var storemanager
     */
    protected $storemanager;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Context $context
     * @param Csv $csv
     * @param DirectoryList $directoryList
     * @param ManagerInterface $messageManager
     * @param JsonHelper $jsonHelper
     * @param ResourceConnection $resource
     * @param CustomerFactory $customer
     * @param StoreManagerInterface $storemanager
     * @param Request $request
     * @param Filesystem $filesystem
     * @param UploaderFactory $_uploaderFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Csv $csv,
        DirectoryList $directoryList,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        JsonHelper $jsonHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        CustomerFactory $customer,
        StoreManagerInterface $storemanager,
        Request $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $_uploaderFactory
    ) {
        $this->_csv = $csv;
        $this->_directoryList = $directoryList;
        $this->messageManager = $messageManager;
        $this->_jsonHelper = $jsonHelper;
        $this->_resource = $resource;
        $this->customer = $customer;
        $this->storemanager = $storemanager;
        $this->request = $request;
        $this->_uploaderFactory = $_uploaderFactory;
        $this->_varDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        return parent::__construct($context);
    }

    /**
     * Execute Controller
     */
    public function execute()
    {
        $post = $this->getRequest()->getPost();
        $resultRedirect = $this->resultRedirectFactory->create();
        $files = $this->request->getFiles()->toArray(); // same as $_FIELS
        $mimes = ['application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv'];
        if ($post && isset($files) && $files["blogCsv"]["error"] == 0) {
            if ($files["blogCsv"]["name"] == 'Comment.csv' || $files["blogCsv"]["name"] == 'Author.csv') {
                try {
                    $uploader = $this->_uploaderFactory->create(['fileId' => $files["blogCsv"]]);
                    $workingDir = $this->_varDirectory->getAbsolutePath('tmp/');
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'zip', 'doc', 'csv']);
                    $uploader->setAllowRenameFiles(false);
                    $result = $uploader->save($workingDir);
                    if ($result['file']) {
                        $this->messageManager->addSuccess(__('File has been successfully uploaded'));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
                $resultDirect = $this->importAuthor($files, $resultRedirect);
                if ($resultDirect) {
                    return $resultRedirect->setPath('*/*/');
                }
            } else {
                $this->messageManager->addNotice(__('Only CSV file named as Comment.csv and Author.csv is allowed!'));
                return $resultRedirect->setPath('*/*/');
            }
        } /* ifpostEnds */
    } /* execute ends */

    /**
     * Json Response
     *
     * @param string $response
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($response));
    }

    /**
     * Get customer by email
     *
     * @param string $email
     */
    public function getCustomerByEmail($email)
    {
        $websiteID = $this->storemanager->getStore()->getWebsiteId();
        $customerData = $this->customer->create()->setWebsiteId($websiteID)->loadByEmail($email);
        if ($customerData->getId()) {
            return $customerData->getId();
        }
        return false;
    }

    /**
     * Import importAuthor
     *
     * @param string $files
     * @param string $resultRedirect
     */
    public function importAuthor($files, $resultRedirect)
    {
        $connection = $this->_resource->getConnection();
        $tmpDir = $this->_directoryList->getPath('tmp');
        $ext = 'csv';
        /* Author */
        if ($files["blogCsv"]["name"] == 'Author.csv') {
            $filePath = $tmpDir . "/Author." . $ext;
            $csv = $this->_csv;
            $csv->setDelimiter(',');
            $csvData = $csv->getData($filePath);
            $tableName = $connection->getTableName('magefan_blog_author');
            foreach ($csvData as $row => $data) {
                if ($row == 0) {
                    continue;
                }
                $query = [
                    'author_id' => $data[4],
                    'is_active' => $data[0],
                    'firstname' => $data[1],
                    'lastname' => $data[2],
                    'email' => $data[3],
                    'identifier' => strtolower($data[1]) . "-" . strtolower($data[2]),
                ];
                $connection->insert($tableName, $query);
            } /* foreach ends */
            $this->messageManager->addSuccess('Author Imported Successful!');
            return true;
        }

        /* Comment */
        if ($files["blogCsv"]["name"] == 'Comment.csv') {
            $resultredirection = $this->importComment($files, $resultRedirect);
            if ($resultredirection) {
                return true;
            }

        }
    }

    /**
     * Import importComment
     *
     * @param string $files
     * @param string $resultRedirect
     */
    public function importComment($files, $resultRedirect)
    {
        $connection = $this->_resource->getConnection();
        $tmpDir = $this->_directoryList->getPath('tmp');
        $ext = 'csv';
        $filePath = $tmpDir . "/Comment." . $ext;
        $csv = $this->_csv;
        $csv->setDelimiter(',');
        $csvData = $csv->getData($filePath);
        $tableName = $connection->getTableName('magefan_blog_comment');
        foreach ($csvData as $row => $data) {
            if ($row == 0) {
                continue;
            }

            $email = $data[7];
            $customerId = $this->getCustomerByEmail($email);
            if ($customerId) {
                $data[2] = $customerId;
                $data[5] = '1';
                $query = [
                    'comment_id' => $data[11],
                    'parent_id' => $data[0],
                    'post_id' => $data[1],
                    'customer_id' => $data[2],
                    'store_id' => $data[3],
                    'status' => $data[4],
                    'author_type' => $data[5],
                    'author_nickname' => $data[6],
                    'author_email' => $data[7],
                    'text' => $data[8],
                    'creation_time' => $data[9],
                    'update_time' => $data[10],
                ];

            } else {

                $query = [
                    'comment_id' => $data[11],
                    'parent_id' => $data[0],
                    'post_id' => $data[1],
                    'customer_id' => $data[2],
                    'store_id' => $data[3],
                    'status' => $data[4],
                    'author_type' => $data[5],
                    'author_nickname' => $data[6],
                    'author_email' => $data[7],
                    'text' => $data[8],
                    'creation_time' => $data[9],
                    'update_time' => $data[10],
                ];
            }
            $connection->insert($tableName, $query);
        } /* foreach ends */
        $this->messageManager->addSuccess('Comment Imported Successful!');
        return true;
    }
}
