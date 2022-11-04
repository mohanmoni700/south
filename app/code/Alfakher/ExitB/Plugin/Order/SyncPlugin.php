<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Plugin\Order;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Alfakher\ExitB\Model\ExitbSync;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\MessageQueue\PublisherInterface;
use Alfakher\ExitB\Model\ResourceModel\ExitbOrder\Collection;
use Alfakher\SalesApprove\Controller\Adminhtml\Order\Approve;

/**
 * ExitB order sync
 */
class SyncPlugin
{
    public const TOPIC_NAME = 'exitb.massorder.sync';
    
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ExitbSync
     */
    protected $exitbsync;

    /**
     * @var Json
     */
    protected $json;
    
    /**
     * @var PublisherInterface
     */
    protected $publisher;
    
    /**
     * Check construct
     *
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param Collection $exitborderModel
     * @param ExitbSync $exitbsync
     * @param Json $json
     * @param PublisherInterface $publisher
     */
    public function __construct(
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        Collection $exitborderModel,
        ExitbSync $exitbsync,
        Json $json,
        PublisherInterface $publisher
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
     * @param Approve $subject
     * @param mixed $result
     * @return $result
     */
    public function afterExecute(
        Approve $subject,
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
