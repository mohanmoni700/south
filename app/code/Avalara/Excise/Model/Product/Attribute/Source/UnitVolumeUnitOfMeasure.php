<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Avalara\Excise\Model\Product\Attribute\Source;

class UnitVolumeUnitOfMeasure extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['value' => 1, 'label' => __('CIG')],
            ['value' => 2, 'label' => __('EA')],
            ['value' => 3, 'label' => __('LBR')],
            ['value' => 4, 'label' => __('PAC')],
            ['value' => 5, 'label' => __('ML')],
            ['value' => 6, 'label' => __('ONZ')],
        ];
        return $this->_options;
    }
}
