<?php

namespace Alfakher\MyDocument\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Alfakher\Productpageb2b\Helper\Data;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Context                                             $context
     * @param PageFactory                                         $resultPageFactory
     */

    public function __construct(
        Context $context,
        Data  $helper,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute MyDocument

     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $myDocumentConfigValue = $this->helper->getConfigValue('hookahshisha/my_document/is_enabled');

        if ($myDocumentConfigValue) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('My Documents'));
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('noroute');
            return $resultRedirect;
        }
    }
}
