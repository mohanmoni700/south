<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Block\Email;

use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaListFactory;
use Magento\Framework\Filter\Input\MaliciousCode;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductAlert\Block\Email\Stock;

class ExtendedStock extends Stock
{
    /**
     * @var AreaListFactory
     */
    private AreaListFactory $areaList;

    /**
     * @var Image
     */
    private Image $imageHelper;

    /**
     * Extended constructor
     *
     * @param Context $context
     * @param MaliciousCode $maliciousCode
     * @param PriceCurrencyInterface $priceCurrency
     * @param ImageBuilder $imageBuilder
     * @param AreaListFactory $areaList
     * @param Image $imageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        MaliciousCode $maliciousCode,
        PriceCurrencyInterface $priceCurrency,
        ImageBuilder $imageBuilder,
        AreaListFactory $areaList,
        Image $imageHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $maliciousCode,
            $priceCurrency,
            $imageBuilder,
            $data
        );
        $this->areaList = $areaList;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Retrieve product image.
     *
     * @param Product $product
     * @param $imageId
     * @return string
     */
    public function getImageUrl(Product $product, $imageId)
    {
        $this->areaList->create()->getArea(Area::AREA_FRONTEND)
            ->load(Area::PART_DESIGN);
        return $this->imageHelper->init($product, $imageId)->getUrl();
    }
}
