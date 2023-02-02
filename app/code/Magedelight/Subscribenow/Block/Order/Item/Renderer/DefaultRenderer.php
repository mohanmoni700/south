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

namespace Magedelight\Subscribenow\Block\Order\Item\Renderer;

class DefaultRenderer
{

    /**
     * Get order options and append subscription options.
     *
     * @param type $subject
     * @param type $result
     *
     * @return type
     */
    public function aftergetItemOptions($subject, $result)
    {
        $options = $subject->getOrderItem()->getProductOptions();
        if ($options) {
            $quoteItemId = $subject->getOrderItem()->getQuoteItemId();
            if ($subject->getOrderItem()->getProduct()->getIsSubscription()) {
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
                    $finalAdditionalOption = $aditionalOption;
                    $printCheck = $subject->getRequest()->getActionName();
                    if (is_array($aditionalOption) && !empty($addValue)) {
                        if ($printCheck == 'print') {
                            $finalAdditionalOption = null;
                            $count = 0;
                            foreach ($aditionalOption as $aditionalOptionValue) {
                                $finalAdditionalOption[$count]['label'] = $aditionalOptionValue['label'];
                                $finalAdditionalOption[$count]['value'] = str_replace('<br/>', PHP_EOL, $aditionalOptionValue['value']);
                                ++$count;
                            }
                        }
                        $result = array_merge($result, $finalAdditionalOption);
                    }
                }
            }
        }

        return $result;
    }
}
