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

namespace Magedelight\Subscribenow\Block\Order\Email\Items\Order;

class DefaultOrder
{

    /**
     * @return array
     */
    public function aftergetItemOptions($subject, $result)
    {
        if ($options = $subject->getItem()->getProductOptions()) {
            $quoteItemId = $subject->getItem()->getQuoteItemId();
            if ($subject->getItem()->getProduct()->getIsSubscription()) {
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
                        $count = 0;
                        foreach ($aditionalOption as $aditionalOptionValue) {
                            $finalAdditionalOption[$count]['label'] = $aditionalOptionValue['label'];
                            $finalAdditionalOption[$count]['value'] = str_replace('<br/>', PHP_EOL, $aditionalOptionValue['value']);
                            ++$count;
                        }
                        if ($finalAdditionalOption) {
                            $result = array_merge($result, $finalAdditionalOption);
                        }
                    }
                }
            }
        }

        return $result;
    }
}
