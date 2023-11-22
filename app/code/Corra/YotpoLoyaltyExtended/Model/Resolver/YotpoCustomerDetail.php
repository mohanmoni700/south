<?php
declare(strict_types=1);

namespace Corra\YotpoLoyaltyExtended\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Yotpo\Loyalty\Helper\Data;

/**
 * Get Yotpo Customer Identification detail
 */
class YotpoCustomerDetail implements ResolverInterface
{
    private CustomerRepositoryInterface $customerRepository;
    private GroupRepositoryInterface $groupRepository;
    private Data $yotpoHelper;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param Data $yotpoHelper
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        GroupRepositoryInterface $groupRepository,
        Data $yotpoHelper
    ) {
        $this->customerRepository = $customerRepository;
        $this->groupRepository = $groupRepository;
        $this->yotpoHelper = $yotpoHelper;
    }

    /**
     * Get Customer groupCode
     *
     * @param string $groupId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCustomerGroupCode($groupId): string
    {
        $customerGroup = $this->groupRepository->getById($groupId);
        if ($customerGroup && $customerGroup->getCode()) {
            return $customerGroup->getCode();
        }
        return '';
    }

    /**
     * Resolver to get token tags and id
     *
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (isset($value['model'])) {
            $customer = $value['model'];
            $groupCode = $this->getCustomerGroupCode($customer->getGroupId());
            return [
                'identity' => $customer->getId(),
                'token' => hash('sha256', $customer->getEmail() . $this->yotpoHelper->getSwellApiKey()),
                'tags' =>  '["' . $groupCode . '"]'
            ];
        } else {
            return [
                'identity' => "",
                'token' => "",
                'tags' =>  ""
            ];
        }
    }
}
