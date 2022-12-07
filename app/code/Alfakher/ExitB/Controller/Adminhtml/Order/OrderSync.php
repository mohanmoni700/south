<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Controller\Adminhtml\Order;

use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Alfakher\ExitB\Model\ExitbSync;
use Magento\Sales\Model\Order;
use Magento\Backend\Model\View\Result\Redirect;

/**
 * Class MassSync
 */
class OrderSync extends AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;
    
    /**
     * @var ExitbSync
     */
    protected $exitbsync;

    /**
     * @var Filter
     */
    protected $filter;
    
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     * @param ExitbSync $exitbsync
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        ExitbSync $exitbsync
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->exitbsync = $exitbsync;
    }

    /**
     * Sync selected orders
     *
     * @param  AbstractCollection $collection
     * @return Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $token_value ='';
        $countOrdersync = 0;
        $orderSyncArray = [];
        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }
            $orderSyncArray[$order->getStore()->getWebsiteId()][]=$order->getEntityId();
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

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
