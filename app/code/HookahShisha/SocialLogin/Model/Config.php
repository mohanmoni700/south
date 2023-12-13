<?php
/**
 *
 * @package     HookahShisha
 * @author      Air global
 * @link        https://air.global/
 */

declare (strict_types=1);
namespace HookahShisha\SocialLogin\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * @var array
     */
    protected $data = [];
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $type
     * @param string $field
     * @return string
     */
    public function getProviderValue(string $type, string $field)
    {
        return $this->getValue("social_login/$type/$field");
    }

    /**
     * @param string $path
     * @param string $scope
     * @param bool $loadFromCache
     * @return string
     */
    public function getValue(string $path, string $scope = ScopeInterface::SCOPE_STORE, bool $loadFromCache = true)
    {
        $key = $path.$scope;
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $this->scopeConfig->getValue($path, $scope);
        }
        return $loadFromCache ? $this->data[$key] : $this->scopeConfig->getValue($path, $scope);
    }
}
