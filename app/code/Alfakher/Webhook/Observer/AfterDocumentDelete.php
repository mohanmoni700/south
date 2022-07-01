<?php

namespace Alfakher\Webhook\Observer;

use Magento\Framework\Event\Observer;
use Mageplaza\Webhook\Model\Config\Source\HookType;

class AfterDocumentDelete extends AfterSave
{
    /**
     * Hook Type For Delete Document
     * @var string
     */
    protected $hookType = 'delete_document';
}
