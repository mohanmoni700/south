<?php

namespace Alfakher\Productpageb2b\Pricing\Render;

use Magento\Catalog\Pricing\Price;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\Render;
use Magento\Store\Model\ScopeInterface;

/**
 * Class for final_price box rendering based on the loggedin user
 */
class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    const DISABLE_PRICE_SECTION = 'hookahshisha/productpageb2b/productpageb2b_price_section_for_guest';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\SaleableInterface $saleableItem
     * @param \Magento\Framework\Pricing\Price\PriceInterface $price
     * @param \Magento\Framework\Pricing\Render\RendererPool $rendererPool
     * @param \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface $salableResolver = null
     * @param \Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface $minimalPriceCalculator = null
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\SaleableInterface $saleableItem,
        \Magento\Framework\Pricing\Price\PriceInterface $price,
        \Magento\Framework\Pricing\Render\RendererPool $rendererPool,
        \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface $salableResolver = null,
        \Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface $minimalPriceCalculator = null,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );
    }

    /**
     * Wrap with logged in user
     *
     * @param string $html
     * @return string
     */
    protected function wrapResult($html)
    {
        $scope = ScopeInterface::SCOPE_STORE;

        if ($this->scopeConfig->getValue(self::DISABLE_PRICE_SECTION, $scope)) {
            if ($this->httpContext->getValue(CustomerContext::CONTEXT_AUTH)) {
                return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
                'data-role="priceBox" ' .
                'data-product-id="' . $this->getSaleableItem()->getId() . '" ' .
                'data-price-box="product-id-' . $this->getSaleableItem()->getId() . '"' .
                    '>' . $html . '</div>';
            }
        }
        return '';
        /*$loginUrl = $this->_storeManager->getStore()->getUrl('customer/account/login');
    $createAccountUrl = $this->_storeManager->getStore()->getUrl('customer/account/create');
    $htmlString = 'Only registered users can see the price. Please <a href="' . $loginUrl . '">Sign in</a> or <a href="' . $createAccountUrl . '">create an account</a>';
    return '<div class="message info notlogged" id="review-form"><div>' . $htmlString . '</div></div>';*/
    }
}
