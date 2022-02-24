<?php

namespace Alfakher\HandlingFee\Block\Adminhtml\Sales\Order\Creditmemo;

/**
 * Adding handling fee to credit memo
 *
 * @author af_bv_op
 */
class Fee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|null
     */
    protected $_creditmemo = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        array $data = []
    ) {
        $this->request = $request;
        $this->invoiceRepository = $invoiceRepository;

        parent::__construct($context, $data);
    }

    /**
     * Get source object
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get credit memo
     */
    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }

    /**
     * Calculate the totals
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getCreditmemo();
        $this->getSource();

        if (!$this->getSource()->getOrder()->getHandlingFee()) {
            return $this;
        }

        $requestParams = $this->request->getParams();
        $haveInvoice = 0;
        $amount = 0;

        /* checking for the invoice */
        if (isset($requestParams['invoice_id']) && $requestParams['invoice_id']) {
            try {
                $invoiceData = $this->invoiceRepository->get($requestParams['invoice_id']);
                $amount = $invoiceData->getHandlingFee();
                $haveInvoice = 1;

                $orderHandlingFeeInvoiced = $this->getSource()->getOrder()->getHandlingFeeInvoiced();
                $orderHandlingFeeRefunded = $this->getSource()->getOrder()->getHandlingFeeRefunded();
                $remainingAmountToRefund = $orderHandlingFeeInvoiced - $orderHandlingFeeRefunded;
                if ($remainingAmountToRefund < $amount) {
                    $amount = $remainingAmountToRefund;
                }

            } catch (\Exception $e) {
                $haveInvoice = 0;
            }
        }
        /* if there is no invoice refernace for the credit memo (offline credit memo) */
        if ($haveInvoice == 0) {
            $orderHandlingFeeInvoiced = $this->getSource()->getOrder()->getHandlingFeeInvoiced();
            $orderHandlingFeeRefunded = $this->getSource()->getOrder()->getHandlingFeeRefunded();
            $amount = $orderHandlingFeeInvoiced - $orderHandlingFeeRefunded;
        }

        if ($amount <= 0) {
            return $this;
        }

        $fee = new \Magento\Framework\DataObject(
            [
                'code' => 'handling_fee',
                'strong' => false,
                'value' => $amount,
                'label' => "Handling Fee",
            ]
        );

        $this->getParentBlock()->addTotalBefore($fee, 'grand_total');

        return $this;
    }
}
