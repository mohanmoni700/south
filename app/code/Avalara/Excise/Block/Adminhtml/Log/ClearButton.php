<?php

namespace Avalara\Excise\Block\Adminhtml\Log;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

class ClearButton implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(Context $context)
    {
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $message = __(
            'This will clear any logs that are older than the lifetime set in configuration. ' .
            'Do you want to continue?'
        );
        return [
            'label' => __('Clear Logs Now'),
            'on_click' => "confirmSetLocation('{$message}', '{$this->getButtonUrl()}')"
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    protected function getButtonUrl()
    {
        return $this->urlBuilder->getUrl('*/*/clear');
    }
}
