<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

class Attachment implements Api\AttachmentInterface
{
    /**
     * @var string
     */
    private $content;
    /**
     * @var string
     */
    private $mimeType;
    /**
     * @var string
     */
    private $filename;
    /**
     * @var string
     */
    private $disposition;
    /**
     * @var string
     */
    private $encoding;

    /**
     * @param string $content
     * @param string $mimeType
     * @param string $fileName
     * @param string $disposition
     * @param string $encoding
     */
    public function __construct(
        $content,
        $mimeType,
        $fileName,
        $disposition = Api\AttachmentInterface::DISPOSITION_ATTACHMENT,
        $encoding = Api\AttachmentInterface::ENCODING_BASE64
    ) {
        $this->content = $content;
        $this->mimeType = $mimeType;
        $this->filename = $fileName;
        $this->disposition = $disposition;
        $this->encoding = $encoding;
    }

    /**
     * Get mime type
     *
     * @return mixed
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Get file name
     *
     * @param bool $encoded
     *
     * @return mixed
     */
    public function getFilename($encoded = false)
    {
        if ($encoded) {
            return sprintf('=?utf-8?B?%s?=', base64_encode($this->filename));
        }
        return $this->filename;
    }

    /**
     * Get disposition
     *
     * @return string
     */
    public function getDisposition()
    {
        return $this->disposition;
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Get content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}
