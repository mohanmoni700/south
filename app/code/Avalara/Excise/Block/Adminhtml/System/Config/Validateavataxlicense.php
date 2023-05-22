<?php

namespace Avalara\Excise\Block\Adminhtml\System\Config;

class Validateavataxlicense extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Merchant Account Number Field
     *
     * @var string
     */
    protected $avataxAccountNo = 'tax_avatax_excise_avatax_account_number';

    /**
     * Merchant Licence Key Field
     *
     * @var string
     */
    protected $avataxLicenseKey = 'tax_avatax_excise_avatax_license_key';

    /**
     * Validate API Button Label
     *
     * @var string
     */
    protected $buttonLabel = 'Validate Excise License';

    /**
     * Mode Field
     *
     * @var string
     */
    protected $currentMode = 'tax_avatax_excise_mode';

     /**
      * Get Excise License Field
      *
      * @return string
      */
    public function getAvataxLicenseKeyField()
    {
        return $this->avataxLicenseKey;
    }

    /**
     * Get Avatax Account Number Field
     *
     * @return string
     */
    public function getAvataxAccountNoField()
    {
        return $this->avataxAccountNo;
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
     * @return \Avalara\Excise\Block\Adminhtml\System\Config\Validateexciselicence
     */
    protected function _prepareLayout()
    {
        // @codeCoverageIgnoreStart
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/validateavataxlicense.phtml');
        }
        return $this;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
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
                'ajax_url' => $this->_urlBuilder->getUrl('excise/system_config_validate/validateavataxlicense'),
            ]
        );

        return $this->_toHtml();
        // @codeCoverageIgnoreEnd
    }
}
