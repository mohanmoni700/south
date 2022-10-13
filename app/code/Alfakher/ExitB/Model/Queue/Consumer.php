<?php
namespace Alfakher\ExitB\Model\Queue;

use Magento\Framework\MessageQueue\ConsumerConfiguration;

/**
 * Exitb Order
 */
class Consumer extends ConsumerConfiguration
{
    public const TOPIC_NAME = "exitb.massorder.sync";
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Alfakher\ExitB\Helper\Data
     */
    protected $helperData;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Check construct
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface  $orderRepository
     * @param \Alfakher\ExitB\Helper\Data                  $helperData
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Alfakher\ExitB\Helper\Data $helperData,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->order = $orderRepository;
        $this->helperData = $helperData;
        $this->json = $json;
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
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/mysql_message_queue.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('text message');
            $logger->info(print_r('Request'.$request, true));
            $data = $this->json->unserialize($request, true);

            $order = $this->order->get($data['orderId']);
            $websiteId = $order->getStore()->getWebsiteId();
            $token_value = $this->helperData->tokenAuthentication($websiteId);
            $result = $this->helperData->orderSync($data['orderId'], $token_value);
            
            $logger->info(print_r("order id".$data['orderId'], true));
        } catch (\Exception $e) {
            $logger->info("Error update.product.attribute: " . $e->getMessage());
        }
    }
}
