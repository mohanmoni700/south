<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Avalara\Excise\Model\Product\Attribute\Source;

class UnitOfMeasure extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['value' => 1, 'label' => __('PAK')],
            ['value' => 2, 'label' => __('ECH')],
            ['value' => 3, 'label' => __('OZ')],
        ];
        return $this->_options;
    }
}
