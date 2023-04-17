<?php

namespace Avalara\Excise\Model;

use Magento\Framework\Model\AbstractModel;
use Yandex\Allure\Adapter\Annotation\Description;

class Log extends AbstractModel
{
    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Avalara\Excise\Model\ResourceModel\Log::class);
    }
}
