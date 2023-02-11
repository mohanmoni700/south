<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Model\Adminhtml\Source;

/**
 * Source of option values in a form of value-label pairs
 */
class PaymentAction implements \Magento\Framework\Data\OptionSourceInterface
{
    private const ACTION_AUTHORIZE = 'authorize';
    private const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ACTION_AUTHORIZE,
                'label' => __('Authorize Only'),
            ],
            [
                'value' => self::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture'),
            ]
        ];
    }
}
