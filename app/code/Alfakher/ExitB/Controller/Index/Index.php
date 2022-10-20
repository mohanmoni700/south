<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Alfakher\ExitB\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Single order sync
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Json
     */
    protected $json;

    /**
     * New construct
     *
     * @param Context                  $context
     * @param PageFactory              $pageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param Data                     $helperData
     * @param Curl                     $curl
     * @param Json                     $json
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        OrderRepositoryInterface $orderRepository,
        Data $helperData,
        Curl $curl,
        Json $json
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->order = $orderRepository;
        $this->helperData = $helperData;
        $this->curl = $curl;
        $this->json = $json;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        
        if (isset($id) && !empty($id)) {
            $order = $this->order->get($id);
            $orderData = [];

            $websiteId = $order->getStore()->getWebsiteId();
            if ($this->helperData->isModuleEnabled($websiteId)) {
                $token_value = $this->helperData->tokenAuthentication($websiteId);
                $result = $this->helperData->orderSync($id, $token_value);
            }
        }
    }
}
