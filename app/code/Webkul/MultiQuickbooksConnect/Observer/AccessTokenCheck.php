<?php
/**
 * Webkul MultiQuickbooksConnect AccessTokenCheck Observer.
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Observer;

use Magento\Framework\Event\ObserverInterface;

class AccessTokenCheck implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * controll hit event handler.
     * @param \Webkul\MultiQuickbooksConnect\Helper\Data $helperData
     */
    public function __construct(\Webkul\MultiQuickbooksConnect\Helper\Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * product save event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->helperData->getAccessToken();
        return $this;
    }
}
