<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

class NextEmailInfo
{
    /**
     * @var $templateVars
     */
    private $templateVars;
    /**
     * @var $templateIdentifier
     */
    private $templateIdentifier;

    /**
     * Get template vars
     *
     * @return mixed
     */
    public function getTemplateVars()
    {
        return $this->templateVars;
    }

    /**
     * Set template vars
     *
     * @param array $templateVars
     * @return void
     */
    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;
    }

    /**
     * Get template identifier
     *
     * @return mixed
     */
    public function getTemplateIdentifier()
    {
        return $this->templateIdentifier;
    }

    /**
     * Set template identifier
     *
     * @param string $templateIdentifier
     * @return void
     */
    public function setTemplateIdentifier($templateIdentifier)
    {
        $this->templateIdentifier = $templateIdentifier;
    }
}
