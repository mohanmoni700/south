<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_CatalogImportExport
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\CatalogImportExport\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product as SourceProduct;

class Product extends SourceProduct
{
    /**
     * Map between import file fields and system fields/attributes.
     *
     * @var array
     */
    protected $_fieldsMap = [
        'image' => 'base_image',
        'image_label' => "base_image_label",
        'thumbnail' => 'thumbnail_image',
        'thumbnail_label' => 'thumbnail_image_label',
        self::COL_MEDIA_IMAGE => 'additional_images',
        '_media_image_label' => 'additional_image_labels',
        '_media_is_disabled' => 'hide_from_product_page',
        SourceProduct::COL_STORE => 'store_view_code',
        SourceProduct::COL_ATTR_SET => 'attribute_set_code',
        SourceProduct::COL_TYPE => 'product_type',
        SourceProduct::COL_PRODUCT_WEBSITES => 'product_websites',
        'status' => 'product_online',
        'news_from_date' => 'new_from_date',
        'news_to_date' => 'new_to_date',
        'options_container' => 'display_product_options_in',
        'minimal_price' => 'map_price',
        'msrp' => 'msrp_price',
        'msrp_enabled' => 'map_enabled',
        'special_from_date' => 'special_price_from_date',
        'special_to_date' => 'special_price_to_date',
        'min_qty' => 'out_of_stock_qty',
        'backorders' => 'allow_backorders',
        'min_sale_qty' => 'min_cart_qty',
        'max_sale_qty' => 'max_cart_qty',
        'notify_stock_qty' => 'notify_on_stock_below',
        '_related_sku' => 'related_skus',
        '_related_position' => 'related_position',
        '_crosssell_sku' => 'crosssell_skus',
        '_crosssell_position' => 'crosssell_position',
        '_upsell_sku' => 'upsell_skus',
        '_upsell_position' => 'upsell_position',
        'meta_keyword' => 'meta_keywords',
        '_charcoal_sku' => 'charcoal_skus',
        '_shisha_sku' => 'shisha_skus'
    ];
}
