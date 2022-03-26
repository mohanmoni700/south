<?php

namespace Alfakher\GrossMargin\Block\Adminhtml\Invoice\Create;

/**
 * @author af_bv_op
 */

class PurchaseOrder extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get order from request.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrder()
    {
        if (!$this->order) {
            $orderId = $this->getRequest()->getParam('order_id');
            $this->order = $this->orderRepository->get($orderId);
        }
        return $this->order;
    }
}
