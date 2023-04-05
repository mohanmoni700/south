<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Observer;

use Fooman\EmailAttachments\Model\Api\AttachmentContainerInterface as ContainerInterface;
use Fooman\EmailAttachments\Model\Api\PdfRendererInterface;
use Fooman\EmailAttachments\Model\ContentAttacher;
use Fooman\EmailAttachments\Model\TermsAndConditionsAttacher;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;

abstract class AbstractObserver implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var PdfRendererInterface
     */
    protected $pdfRenderer;
    /**
     * @var TermsAndConditionsAttacher
     */
    protected $termsAttacher;
    /**
     * @var ContentAttacher
     */
    protected $contentAttacher;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param PdfRendererInterface $pdfRenderer
     * @param TermsAndConditionsAttacher $termsAttacher
     * @param ContentAttacher $contentAttacher
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        PdfRendererInterface $pdfRenderer,
        TermsAndConditionsAttacher $termsAttacher,
        ContentAttacher $contentAttacher
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->pdfRenderer = $pdfRenderer;
        $this->termsAttacher = $termsAttacher;
        $this->contentAttacher = $contentAttacher;
    }

    /**
     * Method to attachContent
     *
     * @param string $content
     * @param string $pdfFilename
     * @param string $mimeType
     * @param ContainerInterface $attachmentContainer
     * @return void
     */
    public function attachContent($content, $pdfFilename, $mimeType, ContainerInterface $attachmentContainer)
    {
        $this->contentAttacher->addGeneric($content, $pdfFilename, $mimeType, $attachmentContainer);
    }

    /**
     * Method to attach pdf
     *
     * @param string $pdfString
     * @param string $pdfFilename
     * @param ContainerInterface $attachmentContainer
     */
    public function attachPdf($pdfString, $pdfFilename, ContainerInterface $attachmentContainer)
    {
        $this->contentAttacher->addPdf($pdfString, $pdfFilename, $attachmentContainer);
    }

    /**
     * Method to attach text
     *
     * @param string $text
     * @param string $filename
     * @param ContainerInterface $attachmentContainer
     */
    public function attachTxt($text, $filename, ContainerInterface $attachmentContainer)
    {
        $this->contentAttacher->addText($text, $filename, $attachmentContainer);
    }

    /**
     * Method to attach html
     *
     * @param string $html
     * @param string $filename
     * @param ContainerInterface $attachmentContainer
     */
    public function attachHtml($html, $filename, ContainerInterface $attachmentContainer)
    {
        $this->contentAttacher->addHtml($html, $filename, $attachmentContainer);
    }

    /**
     * Method to attach terms and condition
     *
     * @param int $storeId
     * @param ContainerInterface $attachmentContainer
     * @return void
     */
    public function attachTermsAndConditions($storeId, ContainerInterface $attachmentContainer)
    {
        $this->termsAttacher->attachForStore($storeId, $attachmentContainer);
    }
}
