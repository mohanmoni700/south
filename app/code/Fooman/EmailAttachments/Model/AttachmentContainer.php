<?php
declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

class AttachmentContainer implements Api\AttachmentContainerInterface
{
    /**
     * @var array
     */
    private $attachments = [];
    /**
     * @var array
     */
    private $dedupIds = [];

    /**
     * Checking for attachments
     *
     * @return bool
     */
    public function hasAttachments()
    {
        return !empty($this->attachments);
    }

    /**
     * Method to add attachment
     *
     * @param Api\AttachmentInterface $attachment
     */
    public function addAttachment(Api\AttachmentInterface $attachment)
    {
        $dedupId = hash('sha256', $attachment->getFilename());
        if (!isset($this->dedupIds[$dedupId])) {
            $this->attachments[] = $attachment;
            $this->dedupIds[$dedupId] = true;
        }
    }

    /**
     * Method Get attachments
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Reset attachments
     *
     * @return void
     */
    public function resetAttachments()
    {
        $this->attachments = [];
        $this->dedupIds = [];
    }
}
