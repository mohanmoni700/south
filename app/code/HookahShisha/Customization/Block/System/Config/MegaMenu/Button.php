<?php

namespace HookahShisha\Customization\Block\System\Config\MegaMenu;

/**
 * MegaMenu
 */
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var _template
     */
    protected $_template = 'HookahShisha_Customization::system/config/megamenu/button.phtml';

    /**
     * @param Context $context
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Url $frontUrl
     * @param array $data
     */

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

    /**
     * ElementHtml
     *
     * @param data $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * GetButtonHtml
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'generate_mega_menu',
                'label' => __('Generate Menu'),
            ]
        );

        return $button->toHtml();
    }

    /**
     * GetAjaxUrl
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        $storeId = $this->request->getParam('store');
        $storeUrl = $this->scopeConfig
            ->getValue('web/secure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
        $url = $storeUrl . 'custom/storeblock/generatemegamenu/store/' . $storeId;
        $url .= "?___store=" . $this->_storeManager->getStore($storeId)->getCode();

        return $url;
    }
}
