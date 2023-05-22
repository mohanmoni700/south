<?php

namespace Avalara\Excise\Block\Adminhtml\System\Config;

class Validateexciselicense extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Merchant Account Number Field
     *
     * @var string
     */
    protected $exciseAccountNo = 'tax_avatax_excise_excise_account_number';

    /**
     * Merchant Licence Key Number Field
     *
     * @var string
     */
    protected $exciseLicenseKey = 'tax_avatax_excise_excise_license_key';

    /**
     * Mode Field
     *
     * @var string
     */
    protected $currentMode = 'tax_avatax_excise_mode';

    /**
     * Validate API Button Label
     *
     * @var string
     */
    protected $buttonLabel = 'Validate Excise License';

    /**
     * Get Excise License Field
     *
     * @return string
     */
    public function getExciseLicenseKeyField()
    {
        return $this->exciseLicenseKey;
    }

    /**
     * Get Excise Account Number Field
     *
     * @return string
     */
    public function getExciseAccountNoField()
    {
        return $this->exciseAccountNo;
    }

    /**
     * Get current mode of setting
     *
     * @return string
     */
    public function getCurrentMode()
    {
        return $this->currentMode;
    }

    /**
     * Set template to itself
     *
     * @return \Avalara\Excise\Block\Adminhtml\System\Config\
     * 
     */
    protected function _prepareLayout()
    {
        // @codeCoverageIgnoreStart
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/validateexciselicense.phtml');
        }
        return $this;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * 
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // @codeCoverageIgnoreStart
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->buttonLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('excise/system_config_validate/validateexciselicense'),
            ]
        );

        return $this->_toHtml();
        // @codeCoverageIgnoreEnd
    }
}
