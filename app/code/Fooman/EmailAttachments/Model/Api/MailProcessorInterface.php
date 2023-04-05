<?php
declare(strict_types=1);

namespace Fooman\EmailAttachments\Model\Api;

interface MailProcessorInterface
{
    /**
     * Method to create multipart message
     *
     * @param array $existingParts
     * @param AttachmentContainerInterface $attachmentContainer
     * @return mixed
     */
    public function createMultipartMessage(
        array $existingParts,
        AttachmentContainerInterface $attachmentContainer
    );
}
