<?php

namespace Alfakher\Webhook\Observer;

use Mageplaza\Webhook\Model\Config\Source\HookType;

class OrderCancelAfter extends AfterSave
{
    /**
     * @var string
     */
    protected $hookType = HookType::ORDER;
}
