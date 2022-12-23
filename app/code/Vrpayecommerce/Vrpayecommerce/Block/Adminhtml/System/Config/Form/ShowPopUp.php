<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Block\Adminhtml\System\Config\Form;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ShowPopUp extends \Magento\Config\Block\System\Config\Form\Field
{
    const POPUP_TEMPLATE = 'system/config/popup/popup.phtml';

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::POPUP_TEMPLATE);
        }
        return $this;
    }

    /**
     * Render popup
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the popup and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $showPopup = $this->_scopeConfig->getValue("payment/vrpayecommerce_general/show_popup",\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (empty($showPopup)) {
            $this->addData(
                [
                    'title' => __('VRPAYECOMMERCE_TT_TERMS'),
                    'message' => str_replace('Admin',
                        '<b data-role="closeBtn">Admin</b>',
                        __('VRPAYECOMMERCE_TT_VERSIONTRACKER')),
                    'buttonlabel' => __('VRPAYECOMMERCE_BACKEND_BT_OK')
                ]
            );

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectWriter  = $objectManager->create('Magento\Framework\App\Config\Storage\WriterInterface');
            $objectWriter->save("payment/vrpayecommerce_general/show_popup",
                "1", "default", 0);

            return $this->_toHtml();
        } else {
            return null;
        }
    }
}