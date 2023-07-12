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

namespace Magedelight\Subscribenow\Block\Adminhtml\Order\Item\Column;

class DefaultColumn
{

    /**
     * Get order options.
     *
     * @return array
     */
    public function aftergetOrderOptions($subject, $result)
    {
        $options = $subject->getItem()->getProductOptions();
        if ($options) {
            $quoteItemId = $subject->getItem()->getQuoteItemId();
            if (!empty($quoteItemId)) {
                $aditionalOption = '';
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $qupteItemOptions = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\Option\Collection')
                        ->addFieldToFilter('item_id', $quoteItemId)
                        ->getData();
                $unserialize = $objectManager->create('Magento\Framework\Serialize\Serializer\Json');
                foreach ($qupteItemOptions as $qupteItemOption) {
                    if ($qupteItemOption['code'] == 'additional_options') {
                        $addValue = $qupteItemOption['value'];
                        $aditionalOption = $unserialize->unserialize($addValue);
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
