<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Observer;

use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Store\Model\ScopeInterface;

class AbstractSendInvoiceObserver extends AbstractObserver
{
    public const XML_PATH_ATTACH_PDF = 'sales_email/invoice/attachpdf';
    public const XML_PATH_ATTACH_AGREEMENT = 'sales_email/invoice/attachagreement';

    /**
     * Execute method
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var InvoiceInterface $invoice
         */
        $invoice = $observer->getInvoice();
        if ($this->pdfRenderer->canRender()
            && $this->scopeConfig->getValue(
                static::XML_PATH_ATTACH_PDF,
                ScopeInterface::SCOPE_STORE,
                $invoice->getStoreId()
            )
        ) {
            $this->contentAttacher->addPdf(
                $this->pdfRenderer->getPdfAsString([$invoice]),
                $this->pdfRenderer->getFileName(__('Invoice') . $invoice->getIncrementId()),
                $observer->getAttachmentContainer()
            );
        }
    }
}
