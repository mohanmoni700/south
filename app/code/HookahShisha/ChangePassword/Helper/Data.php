<?php
namespace HookahShisha\ChangePassword\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $session
    ) {
        $this->_customerSession = $session;
    }

    public function CustomerLogin()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $customer = $this->_customerSession;
            return $customer;

        }
    }
}
