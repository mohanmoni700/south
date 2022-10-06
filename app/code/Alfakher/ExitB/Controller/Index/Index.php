<?php

namespace Alfakher\ExitB\Controller\Index;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Single order sync
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Alfakher\ExitB\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * New construct
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Alfakher\ExitB\Helper\Data $helperData
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Alfakher\ExitB\Helper\Data $helperData,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->order = $orderRepository;
        $this->helperData = $helperData;
        $this->curl = $curl;
        $this->json = $json;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            if (isset($id) && !empty($id)) {
                $order = $this->order->get($id);
                $orderData = [];

                $websiteId = $order->getStore()->getWebsiteId();
                $token_value = $this->helperData->tokenAuthentication($order->getStore()->getWebsiteId());
                $result = $this->helperData->orderSync($id, $token_value);
            } else {
                $this->messageManager->addError(__("Somthing you missing(id) !!!"));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
