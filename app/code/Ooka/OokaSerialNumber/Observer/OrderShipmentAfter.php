<?php
declare(strict_types=1);

namespace Ooka\OokaSerialNumber\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;

class OrderShipmentAfter implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var ShipmentRepositoryInterface
     */
    private ShipmentRepositoryInterface $shipmentRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        $this->productRepository = $productRepository;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * Execute method to set product attribute in shipment level
     *
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Shipment $shipment
         */
        $shipment = $observer->getEvent()->getShipment();
        if (!empty($shipment) && !empty($shipment->getItems())) {
            $shipmentItems = $shipment->getItems();
            $updatedShipmentItems = [];
            foreach ($shipmentItems as $item) {
                $product = $this->productRepository->get($item->getSku(), false, $shipment->getStoreId());
                $item->setData("is_serialize", $product->getData("ooka_require_serial_number"));
                $updatedShipmentItems [] = $item;
            }
            $shipment->setItems($updatedShipmentItems);
            $this->shipmentRepository->save($shipment);
        }
    }
}
