<?php
namespace HookahShisha\Customization\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\GroupedProduct\Model\Product\Link\CollectionProvider\Grouped as GroupedProducts;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;

/**
 * Data provider for Grouped products
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grouped extends \Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier\Grouped
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var array
     */
    protected $uiComponentsConfig = [
        'button_set' => 'grouped_products_button_set',
        'modal' => 'grouped_products_modal',
        'listing' => 'grouped_product_listing',
        'form' => 'product_form',
    ];

    /**
     * @var string
     */
    private static $codeQuantityAndStockStatus = 'quantity_and_stock_status';

    /**
     * @var string
     */
    private static $codeQtyContainer = 'quantity_and_stock_status_qty';

    /**
     * @var string
     */
    private static $codeQty = 'qty';

    /**
     * @var GroupedProducts
     */
    private $groupedProducts;

    /**
     * @var ProductLinkInterfaceFactory
     */
    private $productLinkFactory;

    /**
     * @var Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ImageHelper $imageHelper
     * @param Status $status
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param CurrencyInterface $localeCurrency
     * @param array $uiComponentsConfig
     * @param GroupedProducts $groupedProducts
     * @param ProductLinkInterfaceFactory $productLinkFactory
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ProductLinkRepositoryInterface $productLinkRepository,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        Status $status,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CurrencyInterface $localeCurrency,
        array $uiComponentsConfig = [],
        GroupedProducts $groupedProducts,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        ProductLinkInterfaceFactory $productLinkFactory
    ) {
        $this->stockState = $stockState;
        parent::__construct($locator, $urlBuilder, $productLinkRepository, $productRepository, $imageHelper, $status, $attributeSetRepository, $localeCurrency, $uiComponentsConfig, $groupedProducts, $productLinkFactory);
    }

    /**
     * Fill data column
     *
     * @param ProductInterface $linkedProduct
     * @param ProductLinkInterface $linkItem
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fillData(ProductInterface $linkedProduct, ProductLinkInterface $linkItem)
    {
        /** @var \Magento\Framework\Currency $currency */
        $currency = $this->localeCurrency->getCurrency($this->locator->getBaseCurrencyCode());

        return [
            'id' => $linkedProduct->getId(),
            'name' => $linkedProduct->getName(),
            'sku' => $linkedProduct->getSku(),
            'stock_qty' => number_format($this->stockState->getStockQty($linkedProduct->getId(), $linkedProduct->getWebsiteIds()), 4, null, ''),
            'price' => $currency->toCurrency(sprintf("%f", $linkedProduct->getPrice())),
            'qty' => $linkedProduct->getQty(),
            'position' => $linkedProduct->getPosition(),
            'positionCalculated' => $linkedProduct->getPosition(),
            'thumbnail' => $this->imageHelper
                ->init($linkedProduct, 'product_listing_thumbnail')
                ->setImageFile($linkedProduct->getImage())
                ->getUrl(),
            'type_id' => $linkedProduct->getTypeId(),
            'status' => $this->status->getOptionText($linkedProduct->getStatus()),
            'attribute_set' => $this->attributeSetRepository
                ->get($linkedProduct->getAttributeSetId())
                ->getAttributeSetName(),
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getGrid()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'label' => null,
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_GroupedProduct/js/grouped-product-grid',
                        'addButton' => false,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data.links',
                        'deleteButtonLabel' => __('Remove'),
                        'dataProvider' => $this->uiComponentsConfig['listing'],
                        'map' => [
                            'id' => 'entity_id',
                            'name' => 'name',
                            'sku' => 'sku',
                            'stock_qty' => 'qty',
                            'price' => 'price',
                            'status' => 'status_text',
                            'attribute_set' => 'attribute_set_text',
                            'thumbnail' => 'thumbnail_src',
                        ],
                        'links' => [
                            'insertData' => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' => ['insertData' => false],
                        ],
                        'sortOrder' => 20,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                    ],
                ],
            ],
            'children' => $this->getRows(),
        ];
    }

    /**
     * Fill meta columns
     *
     * @return array
     */
    protected function fillMeta()
    {
        return [
            'id' => $this->getTextColumn('id', true, __('ID'), 10),
            'thumbnail' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Form\Field::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'dataScope' => 'thumbnail',
                            'fit' => true,
                            'label' => __('Thumbnail'),
                            'sortOrder' => 20,
                            'labelVisible' => false,
                        ],
                    ],
                ],
            ],
            'name' => $this->getTextColumn('name', false, __('Name'), 30),
            'attribute_set' => $this->getTextColumn('attribute_set', false, __('Attribute Set'), 40),
            'status' => $this->getTextColumn('status', true, __('Status'), 50),
            'sku' => $this->getTextColumn('sku', false, __('SKU'), 60),
            'stock_qty' => $this->getTextColumn('stock_qty', false, __('Quantity'), 70),
            'price' => $this->getTextColumn('price', true, __('Price'), 80),
            'qty' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => 'qty',
                            'label' => __('Default Quantity'),
                            'fit' => true,
                            'additionalClasses' => 'admin__field-small',
                            'sortOrder' => 90,
                            'validation' => [
                                'validate-number' => true,
                            ],
                            'labelVisible' => false,
                        ],
                    ],
                ],
            ],
            'positionCalculated' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Position'),
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'elementTmpl' => 'Magento_GroupedProduct/components/position',
                            'sortOrder' => 100,
                            'fit' => true,
                            'dataScope' => 'positionCalculated',
                            'labelVisible' => false,
                        ],
                    ],
                ],
            ],
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Form\Element\DataType\Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 110,
                            'fit' => true,
                        ],
                    ],
                ],
            ],
            'position' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Form\Element\DataType\Number::NAME,
                            'formElement' => Form\Element\Input::NAME,
                            'componentType' => Form\Field::NAME,
                            'dataScope' => 'position',
                            'sortOrder' => 120,
                            'visible' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}
