<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Observer;

use Magento\Framework\Event\ObserverInterface;
use Alfakher\ExitB\Model\ResourceModel\ExitbOrder\Collection;
use Alfakher\ExitB\Model\ExitbSync;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Event\Observer;

/**
 * Shisha Word b2c order sync
 */
class OrderSyncShisha implements ObserverInterface
{
    public const TOPIC_NAME = 'exitb.massorder.sync';

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
     * B2c shisha word constaruct
     *
     * @param Collection $exitborderModel
     * @param ExitbSync $exitbsync
     * @param Json $json
     * @param PublisherInterface $publisher
     */
    public function __construct(
        Collection $exitborderModel,
        ExitbSync $exitbsync,
        Json $json,
        PublisherInterface $publisher
    ) {
        $this->exitborderModel = $exitborderModel;
        $this->exitbsync = $exitbsync;
        $this->json = $json;
        $this->publisher = $publisher;
    }

    /**
     * After order edit
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $orderid = $order->getEntityId();
        $websiteId = $order->getStore()->getWebsiteId();

        $orderId = [
            "orderId" => (int) $orderid,
        ];
        
        $paymentMethodCode= $order->getPayment()->getMethod();
        $isVrPayment=$this->isVrPayment($paymentMethodCode);

        if($isVrPayment == false){
            $status = $this->exitborderModel->addFieldToFilter('order_id', $orderid);
            $collection = $status->getColumnValues('sync_status');
            $status_collection = !empty($collection) ? $collection[0] : null;

            if ($this->exitbsync->isModuleEnabled($websiteId) && $status_collection !== '1') {
                $this->publisher->publish(
                    self::TOPIC_NAME,
                    $this->json->serialize($orderId)
                );
            }
        }
    }


    /**
     * Is Vr payment
     *
     * @return boolean
     */
    public function isVrPayment($paymentMethodCode)
    {
        if ($paymentMethodCode == 'vrpayecommerce_directdebit' ||
            $paymentMethodCode == 'vrpayecommerce_creditcard' ||
            $paymentMethodCode == 'vrpayecommerce_ccsaved' ||
            $paymentMethodCode == 'vrpayecommerce_ddsaved'
        ) {
            return true;
        }
        return false;
    }
}
