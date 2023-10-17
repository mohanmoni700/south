<?php

namespace Corra\Veratad\Block\Adminhtml\Order\View;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Corra\Veratad\Model\ResourceModel\OrderExtended\CollectionFactory as OrderExtendedCollectionFactory;

class View extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var OrderExtendedCollectionFactory
     */
    private $orderExtendedCollectionFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param OrderExtendedCollectionFactory $orderExtendedCollectionFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderExtendedCollectionFactory $orderExtendedCollectionFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->orderExtendedCollectionFactory = $orderExtendedCollectionFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Heading Text.
     *
     * @return string
     */
    public function getVeratadInfo()
    {
        return "Veratad Age Verification";
    }

    /**
     * Retrieve Order Identifier
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder() ? $this->getOrder()->getId() : null;
    }

    /**
     * Getting Verated Details
     *
     * @return array|mixed|null
     */
    public function getVeratadDetails()
    {
        $order_id = $this->getOrderId();
        $orderExtended = $this->orderExtendedCollectionFactory->create();
        return $orderExtended->addFieldToFilter('sales_order_id', ['eq' => $order_id])
            ->getFirstItem()->getData();
    }
}
