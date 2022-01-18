<?php
namespace HookahShisha\ChangePassword\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_customerSession;
    protected $httpContext;

    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->_customerSession = $session;
        $this->httpContext = $httpContext;
    }

    public function CustomerLogin()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

        if ($isLoggedIn) {
            return $this->_customerSession;
        }
    }
}
