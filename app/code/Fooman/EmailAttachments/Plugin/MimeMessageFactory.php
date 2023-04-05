<?php
declare(strict_types=1);

namespace Fooman\EmailAttachments\Plugin;

use Fooman\EmailAttachments\Model\Api\MailProcessorInterface;
use Fooman\EmailAttachments\Model\Api\AttachmentContainerInterface;
use Fooman\EmailAttachments\Model\AttachmentContainerFactory;

class MimeMessageFactory
{

    /**
     * @var \Fooman\EmailAttachments\Model\EmailEventDispatcher
     */
    private $emailEventDispatcher;

    /**
     * @var AttachmentContainerFactory
     */
    private $attachmentContainerFactory;

    /**
     * @var MailProcessorInterface
     */
    private $mailProcessor;

    /**
     * @param \Fooman\EmailAttachments\Model\EmailEventDispatcher $emailEventDispatcher
     * @param AttachmentContainerFactory $attachmentContainer
     * @param MailProcessorInterface $mailProcessor
     */
    public function __construct(
        \Fooman\EmailAttachments\Model\EmailEventDispatcher $emailEventDispatcher,
        AttachmentContainerFactory $attachmentContainer,
        MailProcessorInterface $mailProcessor
    ) {
        $this->emailEventDispatcher = $emailEventDispatcher;
        $this->attachmentContainerFactory = $attachmentContainer;
        $this->mailProcessor = $mailProcessor;
    }

    /**
     * Plugin for aroundCreate
     *
     * @param \Magento\Framework\Mail\MimeMessageInterfaceFactory $subject
     * @param \Closure $proceed
     * @param array $data
     * @return mixed
     */
    public function aroundCreate(
        \Magento\Framework\Mail\MimeMessageInterfaceFactory $subject,
        \Closure $proceed,
        array $data = []
    ) {
        if (isset($data['parts'])) {
            $attachmentContainer = $this->attachmentContainerFactory->create();
            $this->emailEventDispatcher->dispatch($attachmentContainer);
            $data['parts'] = $this->attachIfNeeded($data['parts'], $attachmentContainer);
        }
        return $proceed($data);
    }

    /**
     * If attach needed create to create multi message
     *
     * @param array $existingParts
     * @param AttachmentContainerInterface $attachmentContainer
     * @return mixed
     */
    public function attachIfNeeded($existingParts, AttachmentContainerInterface $attachmentContainer)
    {
        if (!$attachmentContainer->hasAttachments()) {
            return $existingParts;
        }
        return $this->mailProcessor->createMultipartMessage($existingParts, $attachmentContainer);
    }
}
