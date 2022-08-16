<?php

declare (strict_types = 1);

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company\Source\Provider\CustomerAttributeOptions;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Websites where to look for customers.
 */
class Website implements OptionSourceInterface
{
    /**
     * Website label string
     */
    public const WEBSITE_LABEL = 'Hookah Wholesalers (B2B)';

    /**
     * @var CustomerAttributeOptions
     */
    private $provider;

    /**
     * @param CustomerAttributeOptions $provider
     */
    public function __construct(CustomerAttributeOptions $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $tempArray = [];
        $websites = $this->provider->loadOptions('website_id');
        foreach ($websites as $key => $website) {
            if ($website['label'] === self::WEBSITE_LABEL) {
                $tempArray = $website;
                unset($websites[$key]);
                break;
            }
        }
        array_unshift($websites, $tempArray);

        return $websites;
    }
}
