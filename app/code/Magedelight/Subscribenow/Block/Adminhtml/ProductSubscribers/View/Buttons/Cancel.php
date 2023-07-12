<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Buttons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Cancel extends Generic implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        
        if ($this->canCancel()) {
            $message = __('Are you sure you want to cancel this subscription profile?');
            $data = [
                'label' => __('Cancel'),
                'on_click' => sprintf('confirmSetLocation("%s","%s");', $message, $this->getCancelUrl()),
                'sort_order' => 25,
            ];
        }
        
        return $data;
    }
    
    /**
     * Subscription Profile Cancel URL
     * @return string
     */
    private function getCancelUrl()
    {
        return $this->getUrl('subscribenow/productsubscribers/cancel', ['id' => $this->getModelId()]);
    }
}
