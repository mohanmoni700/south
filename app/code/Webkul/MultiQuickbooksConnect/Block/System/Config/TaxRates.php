<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Block\System\Config;

use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Model\Calculation\RateFactory;

class TaxRates extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'system/config/tax-rate.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\MultiQuickbooksConnect\Helper\QuickBooks $quickBooksHelper,
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        RateFactory $taxRateFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->taxRateFactory = $taxRateFactory;
    }

    /**
     * Set template to itself.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }

    /**
     * Render button.
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return getTaxRateList.
     * @return string
     */
    public function getTaxRateList()
    {
        $taxRateColl = $this->taxRateFactory->create()->getCollection();
        $rateList = [];
        foreach ($taxRateColl as $taxRate) {
            $rateList[$taxRate->getRate()] = $taxRate->getCode();
        }
        return $rateList;
    }

    /**
     * Get the button and scripts contents.
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'quickbooks_auth',
                'button_label' => __('Update Payment Notify'),
                'onclick' => 'javascript:check(); return false;',
            ]
        );
        return $this->_toHtml();
    }
}
