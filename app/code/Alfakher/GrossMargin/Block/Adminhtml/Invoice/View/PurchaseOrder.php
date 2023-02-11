<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Block\Adminhtml\Invoice\View;

/**
 * @author af_bv_op
 */
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class PurchaseOrder extends \Magento\Backend\Block\Template
{

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @param Context $context
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param array $data [optional]
     */
    public function __construct(
        Context $context,
        InvoiceRepositoryInterface $invoiceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->invoiceRepository = $invoiceRepository;
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
            $invoiceId = $this->getRequest()->getParam('invoice_id');
            $invoice = $this->invoiceRepository->get($invoiceId);
            $this->order = $invoice->getOrder();
        }
        return $this->order;
    }
}
