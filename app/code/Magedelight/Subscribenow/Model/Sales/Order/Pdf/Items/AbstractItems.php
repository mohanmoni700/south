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

namespace Magedelight\Subscribenow\Model\Sales\Order\Pdf\Items;

use Magento\Framework\Serialize\Serializer\Json;

class AbstractItems
{

    /**
     * @var Json
     */
    protected $serialize;

    /**
     *
     * @param Json $serialize
     */
    public function __construct(
        Json $serialize
    ) {
        $this->serialize = $serialize;
    }

    /**
     * Retrieve item options.
     *
     * @return array
     */
    public function aftergetItemOptions($subject, $result)
    {
        $options = $subject->getItem()->getOrderItem()->getProductOptions();
        if ($options) {
            $quoteItemId = $subject->getItem()->getOrderItem()->getQuoteItemId();
            if (!empty($quoteItemId)) {
                $aditionalOption = '';
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $qupteItemOptions = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\Option\Collection')
                        ->addFieldToFilter('item_id', $quoteItemId)
                        ->getData();
                foreach ($qupteItemOptions as $qupteItemOption) {
                    if ($qupteItemOption['code'] == 'additional_options') {
                        $addValue = $qupteItemOption['value'];
                        $aditionalOption = $this->serialize->unserialize($addValue);
                        break;
                    }
                }
                if (is_array($aditionalOption) && !empty($addValue)) {
                    $result = array_merge($result, $aditionalOption);
                }
            }
        }

        return $result;
    }
}
