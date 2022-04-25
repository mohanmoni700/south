<?php

namespace Alfakher\GrossMargin\Block\Adminhtml\Invoice\View;

/**
 * @author af_bv_op
 */

class PurchaseOrder extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->invoiceRepository = $invoiceRepository;
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
            $invoiceId = $this->getRequest()->getParam('invoice_id');
            $invoice = $this->invoiceRepository->get($invoiceId);
            $this->order = $invoice->getOrder();
        }
        return $this->order;
    }
}
