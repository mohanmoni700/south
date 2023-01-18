<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Block\Adminhtml\Invoice\Create;

/**
 * @author af_bv_op
 */
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class PurchaseOrder extends Template
{

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data [optional]
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get order from request.
     *
     * @return OrderInterface
     * @throws NoSuchEntityException
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
