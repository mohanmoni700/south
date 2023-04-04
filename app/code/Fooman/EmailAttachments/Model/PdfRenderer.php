<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

use Magento\Sales\Model\Order\Pdf\AbstractPdf;

class PdfRenderer implements Api\PdfRendererInterface
{
    /**
     * @var AbstractPdf
     */
    protected $pdfRenderer;

    /**
     * @param AbstractPdf $pdfRenderer
     */
    public function __construct(
        AbstractPdf $pdfRenderer
    ) {
        $this->pdfRenderer = $pdfRenderer;
    }

    /**
     * Get pdf as string
     *
     * @param array $salesObject
     * @return string
     * @throws \Zend_Pdf_Exception
     */
    public function getPdfAsString(array $salesObject)
    {
        return $this->pdfRenderer->getPdf($salesObject)->render();
    }

    /**
     * Get file name
     *
     * @param string $input
     * @return string
     */
    public function getFileName($input = '')
    {
        return sprintf('%s.pdf', $input);
    }

    /**
     * Method to can render
     *
     * @return true
     */
    public function canRender()
    {
        return true;
    }
}
