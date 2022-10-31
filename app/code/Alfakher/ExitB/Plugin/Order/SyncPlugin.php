<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Plugin\Order;

/**
 * ExitB order sync
 */
class SyncPlugin
{
    public const TOPIC_NAME = 'exitb.massorder.sync';
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Alfakher\ExitB\Model\ExitbSync
     */
    protected $exitbsync;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;
    
    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    protected $publisher;
    
    /**
     * Check construct
     *
     * @param \Magento\Framework\App\RequestInterface                   $request
     * @param \Magento\Sales\Api\OrderRepositoryInterface               $orderRepository
     * @param \Alfakher\ExitB\Model\ResourceModel\ExitbOrder\Collection $exitborderModel
     * @param \Alfakher\ExitB\Model\ExitbSync                           $exitbsync
     * @param \Magento\Framework\Serialize\Serializer\Json              $json
     * @param \Magento\Framework\MessageQueue\PublisherInterface        $publisher
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Alfakher\ExitB\Model\ResourceModel\ExitbOrder\Collection $exitborderModel,
        \Alfakher\ExitB\Model\ExitbSync $exitbsync,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\MessageQueue\PublisherInterface $publisher
    ) {
        $this->request = $request;
        $this->order = $orderRepository;
        $this->exitborderModel = $exitborderModel;
        $this->exitbsync = $exitbsync;
        $this->json = $json;
        $this->publisher = $publisher;
    }

    /**
     * After sales approve
     *
     * @param \Alfakher\SalesApprove\Controller\Adminhtml\Order\Approve $subject
     * @param mixed $result
     * @return $result
     */
    public function afterExecute(
        \Alfakher\SalesApprove\Controller\Adminhtml\Order\Approve $subject,
        $result
    ) {
        $data = (array) $this->request->getParams();
        $orderId = [
            "orderId" => (int) $data['order_id'],
        ];
        $order = $this->order->get($orderId['orderId']);
        $websiteId = $order->getStore()->getWebsiteId();
        
        $status = $this->exitborderModel->addFieldToFilter('order_id', $orderId['orderId']);
        $collection = $status->getColumnValues('sync_status');
        $status_collection = !empty($collection) ? $collection[0] : null;

        if ($this->exitbsync->isModuleEnabled($websiteId) && $status_collection !== '1') {
            $this->publisher->publish(
                self::TOPIC_NAME,
                $this->json->serialize($orderId)
            );
        }
        return $result;
    }
}
