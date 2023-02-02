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

class Generate extends Generic implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->isNotPending()) {
            $message = __('Are you sure you want to generate next subscription order <br> for this subscription profile?');
            $data = [
                'label' => __('Generate'),
                'class' => 'generate',
                'on_click' => sprintf('confirmSetLocation("%s","%s");', $message, $this->getOrderGenerateUrl()),
                'sort_order' => 40,
            ];
        }
        
        return $data;
    }
    
    public function getOrderGenerateUrl()
    {
        return $this->getUrl('subscribenow/productsubscribers/generate/', ['id' => $this->getModelId()]);
    }
}
