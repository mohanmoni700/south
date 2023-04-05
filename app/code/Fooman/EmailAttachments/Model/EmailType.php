<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

class EmailType
{
    /**
     * @var $type
     */
    private $type;
    /**
     * @var $varCode
     */
    private $varCode;

    /**
     * Email type constructor
     *
     * @param string $type
     * @param int $varCode
     */
    public function __construct(
        $type,
        $varCode
    ) {
        $this->type = $type;
        $this->varCode = $varCode;
    }

    /**
     * Get type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get var code
     *
     * @return mixed
     */
    public function getVarCode()
    {
        return $this->varCode;
    }
}
