<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Resolver;

use Magedelight\SubscribenowGraphQl\Model\Resolver\Subscriptions as Subject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SubscriptionFormatDate
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
     * After plugin
     *
     * @param Subject $subject
     * @param Value $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return mixed|Value
     */
    public function afterResolve(
        Subject     $subject,
                    $result,
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        if (!empty($result['created_at'])) {
            $result['created_at'] = $this->timezone->date($result['created_at'])->format('Y-m-d');
        }

        if (!empty($result['next_occurrence_date'])) {
            $result['next_occurrence_date'] = $this->timezone->date($result['next_occurrence_date'])->format('Y-m-d');
        }

        return $result;
    }
}
