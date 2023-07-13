<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Resolver;

use Magedelight\SubscribenowGraphQl\Model\Resolver\CustomerSubscriptions as Subject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CustomerSubscriptionFormatDate
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    /**
     * After plugin to format subscription data
     *
     * @param Subject $subject
     * @param array $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array
     */
    public function afterResolve(
        Subject     $subject,
        array       $result,
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        if (!empty($result['items'])) {
            foreach ($result['items'] as $item) {
                if (!empty($item['created_at'])) {
                    $item['created_at'] = $this->timezone->date($item['created_at'])
                        ->format('Y-m-d');
                }
                if (!empty($item['next_occurrence_date'])) {
                    $item['next_occurrence_date'] = $this->timezone->date($item['next_occurrence_date'])
                        ->format('Y-m-d');
                }
            }
        }

        return $result;
    }
}
