<?php


namespace Alfakher\Seamlesschex\Controller\Adminhtml\Index;

class Connectiontest extends \Magento\Backend\App\Action
{
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Alfakher\Seamlesschex\Helper\Data $seamlesschexHelper
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Alfakher\Seamlesschex\Helper\Data $seamlesschexHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_seamlesschexHelper = $seamlesschexHelper;
        $this->_curl = $curl;
        $this->_resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * Execute method
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getParam('website_id');
        $data = $this->_seamlesschexHelper->testConnection($websiteId);

        $result = $this->_resultJsonFactory->create();
        $result->setData($data);
        return $result;
    }
}
