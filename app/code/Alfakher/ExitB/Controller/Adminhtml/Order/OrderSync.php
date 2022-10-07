<?php

namespace Alfakher\ExitB\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class MassSync
 */
class OrderSync extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;
    /**
     * @var \Alfakher\ExitB\Helper\Data
     */
    protected $helperData;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     * @param \Alfakher\ExitB\Helper\Data $helperData
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        \Alfakher\ExitB\Helper\Data $helperData
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->helperData = $helperData;
    }

    /**
     * Sync selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $token_value ='';
        $countOrdersync = 0;
        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }
            $orderSyncArray[$order->getStore()->getWebsiteId()][]=$order->getEntityId();
        }

        foreach ($orderSyncArray as $key => $value) {
            $token_value = $this->helperData->tokenAuthentication($key);
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

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
