<?php

namespace Alfakher\Productpageb2b\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\Review;

/**
 * View Detail renderer on click go to the Descscription
 */
class ViewDetailRenderer extends Template
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param ProductRepositoryInterface|\Magento\Framework\Pricing\PriceCurrencyInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storemanager
     * @param array $data = []
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storemanager;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->_coreRegistry->registry('product') && $this->getProductId()) {
            $product = $this->productRepository->getById($this->getProductId());
            $this->_coreRegistry->register('product', $product);
        }
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get review product list url
     *
     * @param bool $useDirectLink allows to use direct link for product reviews page
     * @return string
     */
    public function getViewDetailsUrl($useDirectLink = false)
    {
        $product = $this->getProduct();
        return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
    }
    /**
     * Get product image url
     *
     * @return string
     */
    public function getProductImageUrl()
    {
        $store = $this->_storeManager->getStore();

        $product = $this->getProduct();

        return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
    }
}
