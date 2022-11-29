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

class Resume extends Generic implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->isPaused()) {
            $message = __('Are you sure you want to activate this subscription profile?');
            $data = [
                'label' => __('Resume'),
                'class' => 'primary',
                'on_click' => sprintf('confirmSetLocation("%s","%s");', $message, $this->getResumeUrl()),
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Get Subscription Profile Resume URL
     * @return string
     */
    private function getResumeUrl()
    {
        return $this->getUrl('subscribenow/productsubscribers/resume', ['id' => $this->getModelId()]);
    }
}
