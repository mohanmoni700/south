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

class Edit extends Generic implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        
        if ($this->hideButton() && $this->isEditMode()) {
            $data = $this->getSaveButton();
        }
        
        if (!$this->hideButton()) {
            $data = $this->getEditButton();
        }

        return $data;
    }
    
    /**
     * Edit Button
     * @return array
     */
    private function getEditButton()
    {
        $message = __('Are you sure you want to edit this subscription profile?');
        return [
            'label' => __('Edit Profile'),
            'class' => 'primary',
            'on_click' => sprintf('confirmSetLocation("%s","%s");', $message, $this->getEditUrl()),
            'sort_order' => 50,
        ];
    }
    
    /**
     * Save Button
     * @return array
     */
    private function getSaveButton()
    {
        return [
            'label' => __('Save'),
            'class' => 'primary',
            'on_click' => 'updatSubscriptionForm()',
            'sort_order' => 50,
        ];
    }
    
    /**
     * Get Subscription Profile Edit URL
     * @return string
     */
    private function getEditUrl()
    {
        return $this->getUrl('subscribenow/productsubscribers/view/', ['id' => $this->getModelId(), 'edit' => 'editable']);
    }
}
