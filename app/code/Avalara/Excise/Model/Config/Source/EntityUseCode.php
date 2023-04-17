<?php
/**
 * A Magento 2 module named Avalara/Excise
 * Copyright (C) 2019
 *
 * This file included in Avalara/Excise is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Avalara\Excise\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Avalara\Excise\Api\Rest\ListEntityUseCodesInterface;
use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Model\EntityUseCodeFactory;

/**
 * Option values for customer use code.
 * @codeCoverageIgnore
 */
class EntityUseCode extends AbstractSource
{   
    protected $_entityUseCodeFactory;
    /**
     * EntityUseCode constructor.
     *
     * @param  ListEntityUseCodesInterface $entityUseCodesInterface
     */
    public function __construct(
        ListEntityUseCodesInterface $entityUseCodesInterface,
        EntityUseCodeFactory $_entityUseCodeFactory
    ){
        $this->entityUseCodesInterface = $entityUseCodesInterface;
        $this->_entityUseCodeFactory = $_entityUseCodeFactory;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $result = $this->_entityUseCodeFactory->create();
        $collection = $result->getCollection();
        $resultArray = $this->convertToOptions($collection->getData());
        return $resultArray;
    }

    /**
     * Convert array to dropdown options.
     *
     * @param   array  $result
     * @codeCoverageIgnore
     * @return  array
     */
    protected function convertToOptions($result)
    {
        $optionArr[] = ['label' => 'NONE', 'value'=> 'NONE'];
        if (!is_array($result) || empty($result)) {
            return $optionArr;
        }

        foreach ($result as $value) {
            $optionArr[] = [
                'label' => $value['name'] ,
                'value' => $value['code']
            ];
        }
        return $optionArr;
    }
}
