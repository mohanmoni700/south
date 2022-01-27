<?php

namespace HookahShisha\Customization\Block\System\Config\MegaMenu;

/**
 *
 */
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'HookahShisha_Customization::system/config/megamenu/button.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $frontUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;

        $this->frontUrl = $frontUrl;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'generate_mega_menu',
                'label' => __('Generate Menu'),
            ]
        );

        return $button->toHtml();
    }

    public function getAjaxUrl()
    {
        $storeId = $this->request->getParam('store');
        $storeUrl = $this->scopeConfig->getValue('web/secure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $url = $storeUrl . 'custom/storeblock/generatemegamenu/store/' . $storeId;
        $url .= "?___store=" . $this->_storeManager->getStore($storeId)->getCode();

        return $url;
    }
}
