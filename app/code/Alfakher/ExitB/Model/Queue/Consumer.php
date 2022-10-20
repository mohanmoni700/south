<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Model\Queue;

use Magento\Framework\MessageQueue\ConsumerConfiguration;
use Magento\Sales\Api\OrderRepositoryInterface;
use Alfakher\ExitB\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Message\ManagerInterface;

/**
 * Exitb Order
 */
class Consumer extends ConsumerConfiguration
{
    public const TOPIC_NAME = "exitb.massorder.sync";

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Data
     */
    protected $helperData;
    
    /**
     * @var Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Check construct
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface  $orderRepository
     * @param \Alfakher\ExitB\Helper\Data                  $helperData
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Message\ManagerInterface  $messageManager
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Alfakher\ExitB\Helper\Data $helperData,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->order = $orderRepository;
        $this->helperData = $helperData;
        $this->json = $json;
        $this->messageManager = $messageManager;
    }

    /**
     * Consumer process start
     *
     * @param mixed $request
     * @return void
     */
    public function process($request)
    {
        try {
            $data = $this->json->unserialize($request, true);
            $order = $this->order->get($data['orderId']);
            $websiteId = $order->getStore()->getWebsiteId();
            $token_value = $this->helperData->tokenAuthentication($websiteId);
            $this->helperData->orderSync($data['orderId'], $token_value);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
