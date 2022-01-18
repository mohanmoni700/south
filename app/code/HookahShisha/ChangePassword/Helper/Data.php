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
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_customerSession;
        }
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function customerLoggedIn()
    {
        return (bool) $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Checking customer FirstName
     *
     * @return string
     */
    public function customerFirstName()
    {
        return $this->httpContext->getValue('firstname');
    }

    /**
     * Checking customer LastName
     *
     * @return string
     */
    public function customerLastName()
    {
        return $this->httpContext->getValue('lastname');
    }
}
