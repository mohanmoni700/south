<?php

namespace Alfakher\OrderComment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class CommentBlockConfigProvider implements ConfigProviderInterface
{
    private const CHECKOUT_COMMENT_ENABLED = 'checkout/options/checkout_comments_enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfiguration;

    /**
     * CommentBlockConfigProvider constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
    ) {
        $this->_scopeConfiguration = $scopeConfiguration;
    }

    /**
     * Getonfig
     *
     * @return array
     */
    public function getConfig()
    {
        /** @var array() $displayConfig */
        $displayConfig = [];

        /** @var boolean $enabled */
        $enabled = $this->_scopeConfiguration->getValue(self::CHECKOUT_COMMENT_ENABLED, ScopeInterface::SCOPE_STORE);
        $displayConfig['show_comment_block'] = ($enabled) ? true : false;
        return $displayConfig;
    }
}
