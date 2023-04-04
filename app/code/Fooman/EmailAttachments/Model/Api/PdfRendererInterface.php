<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Model\Api;

interface PdfRendererInterface
{
    /**
     * Get pdf as string
     *
     * @param array $salesObjects
     * @return mixed
     */
    public function getPdfAsString(array $salesObjects);

    /**
     * Get file Name
     *
     * @param string $input
     * @return mixed
     */
    public function getFileName($input = '');

    /**
     * Can render
     *
     * @return mixed
     */
    public function canRender();
}
