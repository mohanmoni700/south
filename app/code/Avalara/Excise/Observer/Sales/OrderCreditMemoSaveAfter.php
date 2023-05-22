<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Avalara\Excise\Observer\Sales;

/**
 * 
 * @codeCoverageIgnore
 */
class OrderCreditMemoSaveAfter implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Avalara\Excise\Model\Queue $queueModel,
        \Avalara\Excise\Model\ResourceModel\Queue\Collection $queueCollection,
        \Avalara\Excise\Helper\Config $config
    ) {
        $this->_request = $request;
        $this->config = $config;
        $this->queueCollection = $queueCollection;
        $this->queueModel = $queueModel;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $config = $this->config;
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order = $creditMemo->getOrder();
        $isVirtual = $order->getIsVirtual();
        $address = $isVirtual ? $creditMemo->getBillingAddress() : $creditMemo->getShippingAddress();
        $storeId = $creditMemo->getStoreId();
        $isTaxableAddress = $config->isAddressTaxable($address, $storeId);
        if ($isTaxableAddress && $config->getExciseTaxMode($creditMemo->getStoreId())=="Estimate tax and Submit transaction to AvaTax") {
            $QueueDataFilter = $this->queueCollection
                                ->addFieldToFilter('entity_type_code', 'creditmemo')
                                ->addFieldToFilter('increment_id', $creditMemo->getIncrementId());
                    
            if ($QueueDataFilter->getSize()<=0) {
                $QueueData = $this->queueModel;
                $currentDateTime = $config->getTimeZoneObject()->date()->format('Y-m-d H:i:s');
                $data = [
                    "created_at"=>$currentDateTime,
                    "updated_at"=>$currentDateTime,
                    "store_id"=>$creditMemo->getStoreId(),
                    "entity_type_id"=>7,
                    "entity_type_code"=>'creditmemo',
                    "entity_id"=>$creditMemo->getEntityId(),
                    "increment_id"=>$creditMemo->getIncrementId(),
                    "queue_status"=>'Pending',
                    "attempts"=>0,
                    "message"=>'',
                ];
                $QueueData->setData($data);
                $QueueData->save();
            }
        }
    }
}
