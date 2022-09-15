<?php
namespace Alfakher\OutOfStockProduct\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Catalog\Model\Product;

class Select extends \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Select
{
    /**
     * @var string
     */
    protected $_template = 'Alfakher_OutOfStockProduct::catalog/product/composite/fieldset/options/type/select.phtml';

    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;
    /**
     * [__construct]
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param Product $product
     * @param array $data
     * @param SecureHtmlRenderer|null $htmlRenderer
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        Product $product,
        array $data = [],
        ?SecureHtmlRenderer $htmlRenderer = null
    ) {
        parent::__construct(
            $context,
            $jsonEncoder,
            $catalogData,
            $registry,
            $string,
            $mathRandom,
            $cartHelper,
            $taxData,
            $pricingHelper,
            $data,
            $htmlRenderer,
        );
        $this->stockRegistry = $stockRegistry;
        $this->product=$product;
        $this->secureRenderer = $htmlRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * @inheritdoc
     */
    public function setValidationContainer($elementId, $containerId)
    {
        $scriptString = 'document.getElementById(\'' .
            $elementId .
            '\').advaiceContainer = \'' .
            $containerId .
            '\';';

        return $this->secureRenderer->renderTag('script', [], $scriptString, false);
    }
    /**
     * [getValidateProductForDropdown]
     *
     * @param mixed $selectionSku
     * @return mixed
     */
    public function getValidateProductForDropdown($selectionSku)
    {
        $productId=$this->product->getIdBySku($selectionSku);
        if ($productId) {
            try {
                $stockItem = $this->stockRegistry->getStockItem($productId);
                $isInStock = $stockItem ? $stockItem->getIsInStock() : false;
                if ($isInStock == true) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Exception $e) {

            }
        }
    }
}
