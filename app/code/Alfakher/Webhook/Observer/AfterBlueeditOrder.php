<?php

namespace Alfakher\Webhook\Observer;

use Magento\Framework\Event\Observer;
use Mageplaza\Webhook\Model\Config\Source\HookType;

class AfterBlueeditOrder extends AfterSave
{
    /**
     * Hook Type For Delete Document
     * @var string
     */
    protected $hookType = 'update_order';
}
