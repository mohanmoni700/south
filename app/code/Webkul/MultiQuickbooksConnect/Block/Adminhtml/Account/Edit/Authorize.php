<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MultiQuickbooksConnect
 * @author      Webkul
 * @copyright   Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit;

class Authorize extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MultiQuickbooksConnect\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MultiQuickbooksConnect\Helper\Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->jsonHelper = $helperData->getJsonHelper();
        parent::__construct($context, $data);
    }

    /**
     * Return ajax url for button.
     * @return string
     */
    public function getGrantUrl()
    {
        $id = $this->getRequest()->getParam('id');
        $integratesWith = $this->_scopeConfig->getValue(
            'wk_multi_quickbooks_connect/general_settings/app_integrates_with'
        );
        $path = ($integratesWith == 'oauth2') ? 'multiquickbooksconnect/oauth/oauth2' : '';
        return $this->getBaseUrl().$path;
    }
}
