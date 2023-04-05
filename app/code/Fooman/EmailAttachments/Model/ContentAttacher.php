<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

use Fooman\EmailAttachments\Model\Api\AttachmentContainerInterface as ContainerInterface;

class ContentAttacher
{
    public const MIME_PDF = 'application/pdf';
    public const TYPE_OCTETSTREAM = 'application/octet-stream';
    public const MIME_TXT = 'text/plain';
    public const MIME_HTML = 'text/html';
    /**
     * @var AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @param AttachmentFactory $attachmentFactory
     */
    public function __construct(
        AttachmentFactory $attachmentFactory
    ) {
        $this->attachmentFactory = $attachmentFactory;
    }

    /**
     * Method to add pdf
     *
     * @param string $pdfString
     * @param string $pdfFilename
     * @param ContainerInterface $attachmentContainer
     * @return void
     */
    public function addPdf($pdfString, $pdfFilename, ContainerInterface $attachmentContainer)
    {
        $this->addGeneric($pdfString, $pdfFilename, self::MIME_PDF, $attachmentContainer);
    }

    /**
     * Method to add generic
     *
     * @param string $content
     * @param string $filename
     * @param string $mimeType
     * @param ContainerInterface $attachmentContainer
     * @return void
     */
    public function addGeneric($content, $filename, $mimeType, ContainerInterface $attachmentContainer)
    {
        $attachment = $this->attachmentFactory->create(
            [
                'content' => $content,
                'mimeType' => $mimeType,
                'fileName' => $filename
            ]
        );
        $attachmentContainer->addAttachment($attachment);
    }

    /**
     * Method to add text
     *
     * @param string $text
     * @param string $filename
     * @param ContainerInterface $attachmentContainer
     * @return void
     */
    public function addText($text, $filename, ContainerInterface $attachmentContainer)
    {
        $this->addGeneric($text, $filename, self::MIME_TXT, $attachmentContainer);
    }

    /**
     * Method to add html
     *
     * @param string $html
     * @param string $filename
     * @param ContainerInterface $attachmentContainer
     * @return void
     */
    public function addHtml($html, $filename, ContainerInterface $attachmentContainer)
    {
        $this->addGeneric($html, $filename, self::MIME_HTML, $attachmentContainer);
    }
}
