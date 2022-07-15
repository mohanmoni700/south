<?php
namespace Alfakher\RmaCustomization\ViewModel;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Data implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $url
     *
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UrlInterface $url
    ) {
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->url = $url;
    }

    /**
     * Get url for rma create
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getReturnCreateUrl($order)
    {
        if ($this->httpContext->getValue('customer_id')) {
            return $this->url->getUrl('returnall/returns/create', ['order_id' => $order->getId()]);
        } else {
            return $this->url->getUrl('returnall/guest/create', ['order_id' => $order->getId()]);
        }
    }
    /**
     * Get url for rma submit
     *
     * @param OrderInterface $order
     * @return string
     * @since 101.1.0
     */
    public function getReturnSubmitUrl($order)
    {
        if ($this->httpContext->getValue('customer_id')) {
            return $this->url->getUrl('returnall/returns/submit', ['order_id' => $order->getId()]);
        } else {
            return $this->url->getUrl('returnall/guest/submit', ['order_id' => $order->getId()]);
        }
    }
}
