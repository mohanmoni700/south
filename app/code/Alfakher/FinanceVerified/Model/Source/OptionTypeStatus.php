<?php
namespace Alfakher\FinanceVerified\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class OptionTypeStatus
 */
class OptionTypeStatus implements OptionSourceInterface
{
    /**
     * Array
     *
     * @var array
     */
    protected $options;
    /**
     * Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $typesOfStatus = [
            1 => 'Yes',
            0 => 'No',
        ];
        $options = [];
        foreach ($typesOfStatus as $key => $typeOfStatus) {
            $options[] = [
                'label' => $typeOfStatus,
                'value' => $key,
            ];
        }

        return $options;
    }
}
