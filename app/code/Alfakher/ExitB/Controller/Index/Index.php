<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Controller\Index;

use Magento\Sales\Api\Data\OrderInterface;

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
     * New construct
     *
     * @param \Magento\Framework\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory   $pageFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface  $orderRepository
     * @param \Alfakher\ExitB\Helper\Data                  $helperData
     * @param \Magento\Framework\HTTP\Client\Curl          $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Alfakher\ExitB\Helper\Data $helperData,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $json
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
