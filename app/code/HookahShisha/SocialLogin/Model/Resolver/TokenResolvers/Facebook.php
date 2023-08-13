<?php
/**
 *
 * @package     HookahShisha
 * @author      Air global
 * @link        https://air.global/
 */

declare (strict_types=1);
namespace HookahShisha\SocialLogin\Model\Resolver\TokenResolvers;

use Facebook\Exceptions\FacebookSDKException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use HookahShisha\SocialLogin\Model\Config;
use HookahShisha\SocialLogin\Model\Resolver\TokenResolverInterface;
use HookahShisha\SocialLogin\Helper\Customer as CustomerHelper;

class Facebook implements TokenResolverInterface
{
    /**
     * @var Config
     */
    private Config $config;
    /**
     * @var CustomerHelper
     */
    private CustomerHelper $customerHelper;

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
    public function resolve(string $token): CustomerInterface
    {
        if (!$this->config->getProviderValue('facebook', 'enabled')) {
            throw new LocalizedException(__('Social login with Facebook is disabled'));
        }
        try {
            $fb = new \Facebook\Facebook([
                'app_id' => $this->config->getProviderValue('facebook', 'app_id'),
                'app_secret' => $this->config->getProviderValue('facebook', 'app_secret'),
                'graph_api_version' => 'v17.0'
            ]);
            $response = $fb->get('/me?fields=id,email,first_name,last_name', $token);
            $user = $response->getGraphUser();
            if ($user && $user->getEmail()) {
                $data = [
                    'email' => $user->getEmail(),
                    'firstname' => $user->getFirstName(),
                    'lastname' => $user->getLastName()
                ];
                return $this->customerHelper->createOrGetCustomer($data);
            } else {
                throw new LocalizedException(__('Could not retrieve your Facebook details'));
            }
        } catch (FacebookSDKException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
