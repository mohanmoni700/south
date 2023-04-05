<?php
declare(strict_types=1);

namespace Fooman\EmailAttachments\Model\Api;

interface AttachmentInterface
{
    public const ENCODING_BASE64          = 'base64';
    public const DISPOSITION_ATTACHMENT   = 'attachment';

    /**
     * Get Mime type
     *
     * @return mixed
     */
    public function getMimeType();

    /**
     * Get file name
     *
     * @param bool $encoded
     * @return mixed
     */
    public function getFilename($encoded = false);

    /**
     * Get disposition
     *
     * @return mixed
     */
    public function getDisposition();

    /**
     * Get encoding
     *
     * @return mixed
     */
    public function getEncoding();

    /**
     * Get content
     *
     * @return mixed
     */
    public function getContent();
}
