<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

/** @noinspection MessDetectorValidationInspection */

namespace MageModule\Core\Model\ResourceModel\Entity\Attribute;

use Magento\Framework\Exception\LocalizedException;
use MageModule\Core\Model\Entity\Attribute;

/**
 * Class AbstractCollection
 *
 * @package MageModule\Core\Model\ResourceModel\Entity\Attribute
 */
abstract class AbstractCollection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
{
    /**
     * @var string
     */
    protected $entityTypeCode;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_itemObjectClass = Attribute::class;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _initSelect()
    {
        $select = $this->getSelect()->from(
            ['main_table' => $this->getResource()->getMainTable()]
        );

        $select->join(
            ['entity_type_table' => $this->getResource()->getTable('eav_entity_type')],
            'main_table.entity_type_id = entity_type_table.entity_type_id',
            ''
        );

        $select->where('entity_type_table.entity_type_code = ?', $this->entityTypeCode);

        return $this;
    }
}
