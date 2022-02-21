<?php
namespace Alfakher\MyDocument\Controller\Adminhtml\Document;

use Alfakher\MyDocument\Helper\Data;
use Alfakher\MyDocument\Model\MyDocument;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Saveform extends \Magento\Backend\App\Action implements HttpPostActionInterface
{

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param MyDocument $documentModel
     * @param Data $helper
     * @param array $data = []
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        MyDocument $documentModel,
        Data $helper,
        array $data = []
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->documentModel = $documentModel;
        $this->helper = $helper;
        $this->resultRedirectFactory = $resultRedirectFactory;
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

        try {
            $newArray = [];
            foreach ($post['mydocument_id'] as $key => $value) {
                $newArray[$key]['mydocument_id'] = $post['mydocument_id'][$key];
                $newArray[$key]['status'] = empty($post['message'][$key]) ? 1 : 0;
                $newArray[$key]['message'] = $post['message'][$key];
            }

            foreach ($newArray as $key => $value) {
                $entity = $this->documentModel->load($value['mydocument_id']);
                if ($entity) {
                    $entity->setStatus($value['status']);
                    $entity->setMessage($value['message']);
                    $entity->save();
                }
            }
            $this->messageManager->addSuccess(__('The data has been saved.'));

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("Something went wrong."));
        }

        $mail = $this->helper->sendMail($newArray, $customerid);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();
        return $resultRedirect;
    }
}
