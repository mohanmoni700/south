<?php
namespace HookahShisha\ChangePassword\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Customer
     *
     * @var [type]
     */
    protected $_customerSession;

    /**
     * Context
     *
     * @var [type]
     */
    protected $httpContext;

    /**
     * [__construct]
     *
     * @param \Magento\Customer\Model\Session                    $session
     * @param \Magento\Framework\App\Http\Context                $httpContext
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_customerSession = $session;
        $this->httpContext = $httpContext;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * [CustomerLogin]
     */
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
    /**
     * [getResetHeaderMessageConfig]
     *
     * @return [type]
     */
    public function getResetHeaderMessageConfig()
    {
        return $this->scopeConfig
            ->getValue(
                'hookahshisha/reset_password_group/reset_password_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}
