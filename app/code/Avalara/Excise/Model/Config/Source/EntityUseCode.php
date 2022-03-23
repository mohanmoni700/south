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

/**
 * Option values for customer use code.
 */
class EntityUseCode extends AbstractSource
{   
    /**
     * EntityUseCode constructor.
     *
     * @param  ListEntityUseCodesInterface $entityUseCodesInterface
     */
    public function __construct(
        ListEntityUseCodesInterface $entityUseCodesInterface
    ){
        $this->entityUseCodesInterface = $entityUseCodesInterface;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $type = Constants::AVALARA_API;
        return $this->entityUseCodesInterface->getEntityUseCodes(null, $type);
        
    }
}
