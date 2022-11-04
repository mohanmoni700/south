<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Model;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * Sync status code
     */
    public const STATUS_DONE = 1;

    /**
     * Pending status code
     */
    public const STATUS_PENDING = 2;
    
    /**
     * Not Done status code
     */
    public const STATUS_NOT_DONE = 3;

    /**
     * Get the options array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_DONE => __('Sync Done'),
            self::STATUS_PENDING => __('In Progress'),
            self::STATUS_NOT_DONE => __('Failed')
        ];
    }

    /**
     * Options to Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
}
