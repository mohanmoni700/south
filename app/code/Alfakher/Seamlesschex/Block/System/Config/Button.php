<?php

namespace Alfakher\Seamlesschex\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends Field
{
    /**
     * Template path
     *
     * @var string $_template
     */
    protected $_template = 'Alfakher_Seamlesschex::system/config/button.phtml';

    /**
     * Construct
     *
     * @param Context $context
     * @param array $data
     */
    /*public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }*/

    /**
     * Render
     *
     * @param AbstractElement $element
     */
    public function render(
        AbstractElement $element
    ) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Element html
     *
     * @param AbstractElement $element
     */
    protected function _getElementHtml(
        AbstractElement $element
    ) {
        return $this->_toHtml();
    }

    /**
     * Url
     */
    public function getCustomUrl()
    {
        return $this->getUrl('seamlesschex/index/connectiontest');
    }

    /**
     * Button html
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()
        ->createBlock(\Magento\Backend\Block\Widget\Button::class)
        ->setData([
            'id' => 'seamlesschex_test',
            'label' => __('Test')
        ]);
        return $button->toHtml();
    }
}
