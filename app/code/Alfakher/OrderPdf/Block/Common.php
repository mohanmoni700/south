<?php

namespace Alfakher\OrderPdf\Block;

class Common extends \Magento\Framework\View\Element\Template
{
    /**
     * [$scopeInterface scopeInterface]
     *
     * @var $scopeInterface
     */
    protected $scopeInterface;

    /**
     * [__construct description]
     *
     * @param \Magento\Framework\View\Element\Template\Context   $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Theme\Block\Html\Header\Logo              $logo
     * @param \Magento\Framework\Registry                        $registry
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_logo = $logo;
        $this->_coreRegistry = $registry;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    /**
     * [getHeaderConfig description]
     *
     * @return this
     */
    public function getHeaderConfig()
    {
        $headerText = $this->scopeConfig
            ->getValue(
                'hookahshisha/order_pdf_group/pdf_header',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        return $headerText;
    }
    /**
     * [getFooterConfig description]
     *
     * @return getFooterConfig
     */
    public function getFooterConfig()
    {
        $footerText = $this->scopeConfig
            ->getValue(
                'hookahshisha/order_pdf_group/pdf_footer',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        return $footerText;
    }
    /**
     * [getLogoUrl description]
     *
     * @return getLogoUrl
     */
    public function getLogoUrl()
    {
        $footerText = $this->scopeConfig
            ->getValue(
                'sales/identity/logo',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        return $footerText;
    }
    /**
     * [getLogoSrc description]
     *
     * @return getLogoSrc
     */
    public function getLogoSrc()
    {
        return $this->_logo->getLogoSrc();
    }
    /**
     * [getOrder description]
     *
     * @return getOrder
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
    /**
     * [getInvoiceNumber description]
     *
     * @return getInvoiceNumber
     */
    public function getInvoiceNumber()
    {
        $invoicesNumber = [];
        $invoices = $this->getOrder()->getInvoiceCollection();
        foreach ($invoices as $key => $value) {
            $invoicesNumber[] = $value->getIncrementId();
        }
        return $invoicesNumber;
    }
    /**
     * [getMediaUrl description]
     *
     * @return getMediaUrl
     */
    public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }
}
