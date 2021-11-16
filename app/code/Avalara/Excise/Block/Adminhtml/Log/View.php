<?php

namespace Avalara\Excise\Block\Adminhtml\Log;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Form widget for viewing log
 */
class View extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Avalara\Excise\Model\Log
     */
    protected $currentLog;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Add back button
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'onclick' => "setLocation('" . $this->_urlBuilder->getUrl('excise/log') . "')",
                'class' => 'back'
            ]
        );
    }

    /**
     * Get log model
     *
     * @return \Avalara\Excise\Model\Log
     */
    public function getLog()
    {
        if (null === $this->currentLog) {
            $this->currentLog = $this->coreRegistry->registry('current_log');
        }
        return $this->currentLog;
    }
}
