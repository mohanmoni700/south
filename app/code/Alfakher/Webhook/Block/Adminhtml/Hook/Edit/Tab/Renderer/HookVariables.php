<?php

namespace Alfakher\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogEavAttr;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Rma\Model\ResourceModel\Item;
use Magento\Rma\Model\ResourceModel\Rma;
use Magento\Rma\Model\ResourceModel\Shipping;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Address;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as CreditmemoResource;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\ResourceModel\Order\Status\History as OrderStatusResource;
use Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer\Body;
use Mageplaza\Webhook\Block\Adminhtml\LiquidFilters;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\HookFactory;

class HookVariables extends Body
{
    /**
     * @var MyDocument
     */
    protected $myDocResource;
    /**
     * @var Item
     */
    protected $rmaItemResource;
    /**
     * @var Rma
     */
    protected $rmaResource;
    /**
     * @var Shipping
     */
    protected $rmShippingResource;

    /**
     * Body constructor.
     *
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param InvoiceResource $invoiceResource
     * @param ShipmentResource $shipmentResource
     * @param CreditmemoResource $creditmemoResource
     * @param OrderStatusResource $orderStatusResource
     * @param CustomerResource $customerResource
     * @param Quote $quoteResource
     * @param CatalogEavAttr $catalogEavAttribute
     * @param CategoryFactory $categoryFactory
     * @param LiquidFilters $liquidFilters
     * @param HookFactory $hookFactory
     * @param Subscriber $subscriber
     * @param Address $addressResource
     * @param MyDocument $myDocResource
     * @param Item $rmaItemResource
     * @param Rma $rmaResource
     * @param Shipping $rmShippingResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        InvoiceResource $invoiceResource,
        ShipmentResource $shipmentResource,
        CreditmemoResource $creditmemoResource,
        OrderStatusResource $orderStatusResource,
        CustomerResource $customerResource,
        Quote $quoteResource,
        CatalogEavAttr $catalogEavAttribute,
        CategoryFactory $categoryFactory,
        LiquidFilters $liquidFilters,
        HookFactory $hookFactory,
        Subscriber $subscriber,
        Address $addressResource,
        MyDocument $myDocResource,
        Item $rmaItemResource,
        Rma $rmaResource,
        Shipping $rmShippingResource,
        array $data = []
    ) {
        $this->myDocResource = $myDocResource;
        $this->rmaItemResource = $rmaItemResource;
        $this->rmaResource = $rmaResource;
        $this->rmShippingResource = $rmShippingResource;
        parent::__construct(
            $context,
            $orderFactory,
            $invoiceResource,
            $shipmentResource,
            $creditmemoResource,
            $orderStatusResource,
            $customerResource,
            $quoteResource,
            $catalogEavAttribute,
            $categoryFactory,
            $liquidFilters,
            $hookFactory,
            $subscriber,
            $addressResource,
            $data
        );
    }

    /**
     * Get attributes collection according to the hook type
     *
     * @return array
     */
    public function getHookAttrCollection()
    {
        $hookType = $this->getHookType();

        switch ($hookType) {
            case HookType::NEW_ORDER_COMMENT:
                $collectionData = $this->orderStatusResource->getConnection()
                    ->describeTable($this->orderStatusResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::NEW_INVOICE:
                $collectionData = $this->invoiceResource->getConnection()
                    ->describeTable($this->invoiceResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::NEW_SHIPMENT:
                $collectionData = $this->shipmentResource->getConnection()
                    ->describeTable($this->shipmentResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::NEW_CREDITMEMO:
                $collectionData = $this->creditmemoResource->getConnection()
                    ->describeTable($this->creditmemoResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::NEW_CUSTOMER:
            case HookType::UPDATE_CUSTOMER:
            case HookType::DELETE_CUSTOMER:
            case HookType::CUSTOMER_LOGIN:
                $collectionData = $this->customerResource->loadAllAttributes()->getSortedAttributes();
                $attrCollection = $this->getAttrCollectionFromEav($collectionData);
                break;
            case HookType::NEW_PRODUCT:
            case HookType::UPDATE_PRODUCT:
            case HookType::DELETE_PRODUCT:
                $collectionData = $this->catalogEavAttribute->getCollection()
                    ->addFieldToFilter(AttributeSet::KEY_ENTITY_TYPE_ID, 4);
                $attrCollection = $this->getAttrCollectionFromEav($collectionData);
                break;
            case HookType::NEW_CATEGORY:
            case HookType::UPDATE_CATEGORY:
            case HookType::DELETE_CATEGORY:
                $collectionData = $this->categoryFactory->create()->getAttributes();
                $attrCollection = $this->getAttrCollectionFromEav($collectionData);
                break;
            case HookType::ABANDONED_CART:
                $collectionData = $this->quoteResource->getConnection()
                    ->describeTable($this->quoteResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case HookType::SUBSCRIBER:
                $collectionData = $this->subscriber->getConnection()
                    ->describeTable($this->subscriber->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case "new_document":
            case "update_document":
            case "delete_document":
                $collectionData = $this->myDocResource->getConnection()
                    ->describeTable($this->myDocResource->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
            case "create_rma":
            case "update_rma":
                $rmaCollection = $this->rmaResource->getConnection()
                    ->describeTable($this->rmaResource->getMainTable());
                $rmaAttrCollection = $this->getAttrCollectionFromDb($rmaCollection);

                $rmaShippingCollection = $this->rmShippingResource->getConnection()
                    ->describeTable($this->rmShippingResource->getMainTable());
                $rmaShippingAttrCollection = $this->getAttrCollectionFromDb($rmaShippingCollection);
                $attrCollection = array_merge($rmaAttrCollection, $rmaShippingAttrCollection);
                break;
            default:
                $collectionData = $this->orderFactory->create()->getResource()->getConnection()
                    ->describeTable($this->orderFactory->create()->getResource()->getMainTable());
                $attrCollection = $this->getAttrCollectionFromDb($collectionData);
                break;
        }

        return $attrCollection;
    }
}
