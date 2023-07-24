<?php
/**
 *
 * @package     HookahShisha
 * @author      Air global
 * @link        https://air.global/
 */

declare (strict_types=1);
namespace HookahShisha\SocialLogin\Model\Resolver;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;

interface TokenResolverInterface
{
    /**
     * @param string $token
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function resolve(string $token): CustomerInterface;
}
