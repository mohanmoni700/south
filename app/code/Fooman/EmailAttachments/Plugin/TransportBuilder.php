<?php
declare(strict_types=1);

namespace Fooman\EmailAttachments\Plugin;

class TransportBuilder
{
    /**
     * @var \Fooman\EmailAttachments\Model\NextEmailInfo
     */
    private $nextEmail;

    /**
     * @param \Fooman\EmailAttachments\Model\NextEmailInfo $nextEmailInfo
     */
    public function __construct(
        \Fooman\EmailAttachments\Model\NextEmailInfo $nextEmailInfo
    ) {
        $this->nextEmail = $nextEmailInfo;
    }

    /**
     * Pulgin for beforeSetTemplateIdentifier
     *
     * @param \Magento\Framework\Mail\Template\TransportBuilder $subject
     * @param string $templateIdentifier
     * @return void
     */
    public function beforeSetTemplateIdentifier(
        \Magento\Framework\Mail\Template\TransportBuilder $subject,
        $templateIdentifier
    ) {
        $this->nextEmail->setTemplateIdentifier($templateIdentifier);
    }

    /**
     * Plugin for beforeSetTemplateVars
     *
     * @param \Magento\Framework\Mail\Template\TransportBuilder $subject
     * @param array $templateVars
     * @return void
     */
    public function beforeSetTemplateVars(
        \Magento\Framework\Mail\Template\TransportBuilder $subject,
        $templateVars
    ) {
        $this->nextEmail->setTemplateVars($templateVars);
    }

    /**
     * Plugin for aroundGetTransport
     *
     * @param \Magento\Framework\Mail\Template\TransportBuilder $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetTransport(
        \Magento\Framework\Mail\Template\TransportBuilder $subject,
        \Closure $proceed
    ) {
        $mailTransport = $proceed();
        $this->reset();
        return $mailTransport;
    }

    /**
     * Reset method
     *
     * @return void
     */
    private function reset()
    {
        $this->nextEmail->setTemplateIdentifier(null);
        $this->nextEmail->setTemplateVars(null);
    }
}
