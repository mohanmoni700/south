<?php
/**
 *
 * @package     HookahShisha
 * @author      Air global
 * @link        https://air.global/
 */

declare (strict_types=1);
namespace HookahShisha\SocialLogin\Model\Resolver\TokenResolvers;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use HookahShisha\SocialLogin\Model\Config;
use HookahShisha\SocialLogin\Model\Resolver\TokenResolverInterface;
use HookahShisha\SocialLogin\Helper\Customer as CustomerHelper;

class Google implements TokenResolverInterface
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * Google constructor.
     * @param Config $config
     * @param CustomerHelper $customerHelper
     */
    public function __construct(
        Config $config,
        CustomerHelper $customerHelper
    ) {
        $this->config = $config;
        $this->customerHelper = $customerHelper;
    }

    /**
     * @param string $token
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function resolve(string $token):CustomerInterface
    {
        if (!$this->config->getProviderValue('google', 'enabled')) {
            throw new LocalizedException(__('Social login with Google is disabled'));
        }
        $client = new \Google_Client([
            'client_id' => $this->config->getProviderValue('google', 'client_id'),
            'client_secret' => $this->config->getProviderValue('google', 'client_secret')
        ]);
        $payload = $client->verifyIdToken($token);
        if ($payload && isset($payload['email'])) {
            $data = [
                'email' => $payload['email'] ?? null,
                'firstname' => $payload['given_name'] ?? null,
                'lastname' => $payload['family_name'] ?? null
            ];
            return $this->customerHelper->createOrGetCustomer($data);
        } else {
            throw new LocalizedException(__('Invalid token'));
        }
    }
}
