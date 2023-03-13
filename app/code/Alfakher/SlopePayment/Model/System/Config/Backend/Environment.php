<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Model\System\Config\Backend;

use Magento\Framework\Data\OptionSourceInterface;

class Environment implements OptionSourceInterface
{
    public const ENVIRONMENT_SANDBOX = 'sandbox';
    public const ENVIRONMENT_PRODUCTION = 'production';
    
    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Sandbox',
                'value' => self::ENVIRONMENT_SANDBOX,
            ],
            [
                'label' => 'Production',
                'value' => self::ENVIRONMENT_PRODUCTION,
            ],
        ];
    }
}
