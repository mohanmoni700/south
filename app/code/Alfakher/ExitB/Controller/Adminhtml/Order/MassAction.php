<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Alfakher\ExitB\Model\ResourceModel\ExitbOrder\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Alfakher\ExitB\Model\ExitbSync;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\Exception;

class MassAction extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ExitbSync
     */
    protected $exitbsync;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param ExitbSync $exitbsync
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepository,
        ExitbSync $exitbsync
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->order = $orderRepository;
        $this->exitbsync = $exitbsync;
        parent::__construct($context);
    }

    /**
     * MassUpdate action
     *
     * @return Redirect
     * @throws Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
 
        $token_value ='';
        $countOrdersync = 0;
        foreach ($collection->getItems() as $order) {
            if (!$order->getOrderId()) {
                continue;
            }
            $orderSyncArray[$this->getWebsiteId($order->getOrderId())][]=$order->getOrderId();
        }

        foreach ($orderSyncArray as $websiteId => $value) {
            $token_value = $this->exitbsync->tokenAuthentication($websiteId);
            if (!empty($token_value)) {
                foreach ($value as $keys => $orderId) {
                    $this->exitbsync->orderSync($orderId, $token_value);
                    $countOrdersync++;
                }
            }
        }

        $countNonDeleteOrder = $collection->count() - $countOrdersync;
        if ($countNonDeleteOrder && $countOrdersync) {
            $this->messageManager->addError(__('%1 order(s) were not sync in ExitB.', $countNonDeleteOrder));
        } elseif ($countNonDeleteOrder) {
            $this->messageManager->addError(__('No order(s) were sync.'));
        }
        if ($countOrdersync) {
            $this->messageManager->addSuccess(__('Order Sync In ExitB %1 order(s).', $countOrdersync));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('exitbordersync/index/index');
    }

    /**
     * Get website id
     *
     * @param int $orderId
     */
    public function getWebsiteId($orderId)
    {
        $order = $this->order->get($orderId);
        return $order->getStore()->getWebsiteId();
    }
}
