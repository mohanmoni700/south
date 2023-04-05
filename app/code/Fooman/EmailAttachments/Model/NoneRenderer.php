<?php
declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

class NoneRenderer implements Api\PdfRendererInterface
{
    /**
     * Get pdf as string
     *
     * @param array $salesObject
     * @return string
     */
    public function getPdfAsString(array $salesObject)
    {
        return '';
    }

    /**
     * Get file name
     *
     * @param string $input
     * @return string
     */
    public function getFileName($input = '')
    {
        return sprintf('%s.pdf', $input);
    }

    /**
     * Method to can render
     *
     * @return false
     */
    public function canRender()
    {
        return false;
    }
}
