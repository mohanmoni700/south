<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Block\Adminhtml\Block\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData()
    {

        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'serialnumber_form.serialnumber_form',
                                'actionName' => 'save',
                                'params' => [
                                    false
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
