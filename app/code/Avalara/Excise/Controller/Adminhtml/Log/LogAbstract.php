<?php

namespace Avalara\Excise\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
/**
 * @codeCoverageIgnore
 */
abstract class LogAbstract extends Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Avalara_Excise::manage_avatax');
    }
}
