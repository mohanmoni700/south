<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

use Magento\Framework\Mail\MimePartInterfaceFactory;

class MailProcessor implements Api\MailProcessorInterface
{
    /**
     * @var MimePartInterfaceFactory
     */
    private $mimePartInterfaceFactory;

    /**
     * @param MimePartInterfaceFactory $mimePartInterfaceFactory
     */
    public function __construct(
        MimePartInterfaceFactory $mimePartInterfaceFactory
    ) {
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
    }

    /**
     * Create multipart message
     *
     * @param array $existingParts
     * @param Api\AttachmentContainerInterface $attachmentContainer
     * @return array
     */
    public function createMultipartMessage(
        array $existingParts,
        Api\AttachmentContainerInterface $attachmentContainer
    ) {
        foreach ($attachmentContainer->getAttachments() as $attachment) {
            $mimePart = $this->mimePartInterfaceFactory->create(
                [
                    'content' => $attachment->getContent(),
                    'fileName' => $attachment->getFilename(true),
                    'type' => $attachment->getMimeType(),
                    'encoding' => $attachment->getEncoding(),
                    'disposition' => $attachment->getDisposition()
                ]
            );

            $existingParts[] = $mimePart;
        }

        return $existingParts;
    }
}
