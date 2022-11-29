<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Product\Attribute\Backend;

use Magento\Framework\Serialize\Serializer\Json;

class Subscription extends \Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded
{

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param type $product
     */
    public function beforeSave($product)
    {
        if ($product->hasIsSubscription()) {
            if ($product->getIsSubscription()) {
                parent::beforeSave($product);
            } else {
                $product->unsSubscriptionNow();
            }
        }
    }

    /**
     * @param \Magento\Framework\DataObject $product
     */
    protected function _unserialize(\Magento\Framework\DataObject $product)
    {
        if ($product->hasIsSubscription()) {
            if ($product->getIsSubscription()) {
                $this->jsonSerializer->unserialize($product);
            } else {
                $product->unsSubscriptionNow();
            }
        }
    }
}
