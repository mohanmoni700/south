<?php
declare(strict_types=1);

namespace Fooman\EmailAttachments\Model\Api;

interface AttachmentContainerInterface
{
    /**
     * Has attachment function
     *
     * @return bool
     */
    public function hasAttachments();

    /**
     * Add attachment
     *
     * @param AttachmentInterface $attachment
     */
    public function addAttachment(AttachmentInterface $attachment);

    /**
     * Get attachment
     *
     * @return AttachmentInterface[]
     */
    public function getAttachments();

    /**
     * Reset attachment
     *
     * @return void
     */
    public function resetAttachments();
}
