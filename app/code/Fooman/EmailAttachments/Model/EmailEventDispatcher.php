<?php
declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

use Fooman\EmailAttachments\Model\Api\MailProcessorInterface;

class EmailEventDispatcher
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var NextEmailInfo
     */
    private $nextEmailInfo;

    /**
     * @var EmailIdentifier
     */
    private $emailIdentifier;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param NextEmailInfo $nextEmailInfo
     * @param EmailIdentifier $emailIdentifier
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        NextEmailInfo $nextEmailInfo,
        EmailIdentifier $emailIdentifier
    ) {
        $this->eventManager = $eventManager;
        $this->nextEmailInfo = $nextEmailInfo;
        $this->emailIdentifier = $emailIdentifier;
    }

    /**
     * Method to dispatch
     *
     * @param Api\AttachmentContainerInterface $attachmentContainer
     * @return void
     */
    public function dispatch(Api\AttachmentContainerInterface $attachmentContainer)
    {
        if ($this->nextEmailInfo->getTemplateIdentifier()) {
            $this->determineEmailAndDispatch($attachmentContainer);
        }
    }

    /**
     * Determine email and dispatch
     *
     * @param Api\AttachmentContainerInterface $attachmentContainer
     * @return void
     */
    public function determineEmailAndDispatch(Api\AttachmentContainerInterface $attachmentContainer)
    {
        $emailType = $this->emailIdentifier->getType($this->nextEmailInfo);
        if ($emailType->getType()) {
            $this->eventManager->dispatch(
                'fooman_emailattachments_before_send_' . $emailType->getType(),
                [

                    'attachment_container' => $attachmentContainer,
                    $emailType->getVarCode() => $this->nextEmailInfo->getTemplateVars()[$emailType->getVarCode()]
                ]
            );
        }
    }
}
