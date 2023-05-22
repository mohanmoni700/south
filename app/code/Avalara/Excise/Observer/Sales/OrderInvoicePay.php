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
class OrderInvoicePay implements \Magento\Framework\Event\ObserverInterface
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
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $isVirtual = $order->getIsVirtual();
        $address = $isVirtual ? $invoice->getBillingAddress() : $invoice->getShippingAddress();
        $storeId = $invoice->getStoreId();
        $isTaxableAddress = $this->config->isAddressTaxable($address, $storeId);
        if ($isTaxableAddress && $this->config->getExciseTaxMode($invoice->getStoreId())=="Estimate tax and Submit transaction to AvaTax") {
            $QueueDataFilter = $this->queueCollection
                                ->addFieldToFilter('entity_type_code', 'invoice')
                                ->addFieldToFilter('increment_id', $invoice->getIncrementId());
                    
            if ($QueueDataFilter->getSize()<=0) {
                $QueueData = $this->queueModel;
                $currentDateTime = $this->config->getTimeZoneObject()->date()->format('Y-m-d H:i:s');
                $data = [
                    "created_at"=>$currentDateTime,
                    "updated_at"=>$currentDateTime,
                    "store_id"=>$invoice->getStoreId(),
                    "entity_type_id"=>6,
                    "entity_type_code"=>'invoice',
                    "entity_id"=>$invoice->getEntityId(),
                    "increment_id"=>$invoice->getIncrementId(),
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
