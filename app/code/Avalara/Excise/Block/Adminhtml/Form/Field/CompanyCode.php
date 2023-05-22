<?php

namespace Avalara\Excise\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * @codeCoverageIgnore
 */
class CompanyCode extends Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return parent::_getElementHtml($element) . $this->getLayout()->createBlock(
            \Magento\Backend\Block\Template::class
        )->setData($this->getData())->setTemplate('Avalara_Excise::form/field/company-code.phtml')->toHtml();
    }
}
