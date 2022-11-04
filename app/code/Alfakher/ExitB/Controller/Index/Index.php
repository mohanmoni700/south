<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Alfakher\ExitB\Model\ExitbSync;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Controller\ResultInterface;

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
     * @var ExitbSync
     */
    protected $exitbsync;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * New construct
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param ExitbSync $exitbsync
     * @param Curl $curl
     * @param Json $json
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        OrderRepositoryInterface $orderRepository,
        ExitbSync $exitbsync,
        Curl $curl,
        Json $json
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->order = $orderRepository;
        $this->exitbsync = $exitbsync;
        $this->curl = $curl;
        $this->json = $json;
    }

    /**
     * Execute view action
     *
     * @return mixed
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        
        if (isset($id) && !empty($id)) {
            $order = $this->order->get($id);
            $orderData = [];

            $websiteId = $order->getStore()->getWebsiteId();
            if ($this->exitbsync->isModuleEnabled($websiteId)) {
                $token_value = $this->exitbsync->tokenAuthentication($websiteId);
                $result = $this->exitbsync->orderSync($id, $token_value);
            }
        }
    }
}
