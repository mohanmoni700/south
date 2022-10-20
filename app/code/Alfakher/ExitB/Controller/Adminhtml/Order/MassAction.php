<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Alfakher\ExitB\Model\ResourceModel\ExitbOrder\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Alfakher\ExitB\Helper\Data;

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
     * @var Data
     */
    protected $helperData;

    /**
     * @param \Magento\Backend\App\Action\Context               $context
     * @param \Magento\Ui\Component\MassAction\Filter           $filter
     * @param \Alfakher\ExitB\Model\ResourceModel\ExitbOrder    $collectionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface       $orderRepository
     * @param \Alfakher\ExitB\Helper\Data                       $helperData
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepository,
        Data $helperData
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->order = $orderRepository;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * MassUpdate action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
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
            $token_value = $this->helperData->tokenAuthentication($websiteId);
            if (!empty($token_value)) {
                foreach ($value as $keys => $orderId) {
                    $this->helperData->orderSync($orderId, $token_value);
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

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('exitbordersync/index/index');
    }

    /**
     * Get website id
     *
     * @param int   $orderId
     */
    public function getWebsiteId($orderId)
    {
        $order = $this->order->get($orderId);
        return $order->getStore()->getWebsiteId();
    }
}
