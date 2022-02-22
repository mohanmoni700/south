<?php

namespace Alfakher\HandlingFee\Model\Creditmemo\Total;

/**
 *
 */
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class HandlingFee extends AbstractTotal
{

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        array $data = []
    ) {
        $this->request = $request;
        $this->invoiceRepository = $invoiceRepository;

        parent::__construct($data);
    }

    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $requestParams = $this->request->getParams();
        $haveInvoice = 0;
        $amount = 0;

        /* checking for the invoice */
        if (isset($requestParams['invoice_id']) && $requestParams['invoice_id']) {
            try {
                $invoiceData = $this->invoiceRepository->get($requestParams['invoice_id']);
                $amount = $invoiceData->getHandlingFee();
                $haveInvoice = 1;

                $orderHandlingFeeInvoiced = $creditmemo->getOrder()->getHandlingFeeInvoiced();
                $orderHandlingFeeRefunded = $creditmemo->getOrder()->getHandlingFeeRefunded();
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
            $orderHandlingFeeInvoiced = $creditmemo->getOrder()->getHandlingFeeInvoiced();
            $orderHandlingFeeRefunded = $creditmemo->getOrder()->getHandlingFeeRefunded();
            $amount = $orderHandlingFeeInvoiced - $orderHandlingFeeRefunded;
        }

        if ($amount > 0) {

            if (isset($requestParams['creditmemo']) && isset($requestParams['creditmemo']['handling_fee'])) {
                $newHandlingFee = $requestParams['creditmemo']['handling_fee'];
                if ($newHandlingFee == '') {
                    $newHandlingFee = 0;
                }
                if ($newHandlingFee < 0) {
                    $newHandlingFee = 0;
                }

                if ($amount >= $newHandlingFee) {
                    $amount = $newHandlingFee;
                }
            }

            $creditmemo->setHandlingFee(0);
            $creditmemo->setHandlingFee($amount);

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getHandlingFee());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getHandlingFee());
        }

        return $this;
    }
}
