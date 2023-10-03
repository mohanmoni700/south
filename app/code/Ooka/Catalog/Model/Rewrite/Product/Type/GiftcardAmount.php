<?php

declare(strict_types=1);

namespace Ooka\Catalog\Model\Rewrite\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Type;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\GiftCard\Model\Catalog\Product\Type\Giftcard;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\StoreManagerInterface;
use Ooka\Catalog\Model\GiftCard as AllowedOptions;
use Psr\Log\LoggerInterface;

class GiftcardAmount extends Giftcard
{
    private AllowedOptions $allowedOptions;

    /**
     * @param Option $catalogProductOption
     * @param Config $eavConfig
     * @param Type $catalogProductType
     * @param ManagerInterface $eventManager
     * @param Database $fileStorageDb
     * @param Filesystem $filesystem
     * @param Registry $coreRegistry
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param FormatInterface $localeFormat
     * @param ScopeConfigInterface $scopeConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param AllowedOptions $allowedOptions
     * @param Json|null $serializer
     * @param UploaderFactory|null $uploaderFactory
     * @throws NoSuchEntityException
     */
    public function __construct(
        Option                     $catalogProductOption,
        Config                     $eavConfig,
        Type                       $catalogProductType,
        ManagerInterface           $eventManager,
        Database                   $fileStorageDb,
        Filesystem                 $filesystem,
        Registry                   $coreRegistry,
        LoggerInterface            $logger,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface      $storeManager,
        FormatInterface            $localeFormat,
        ScopeConfigInterface       $scopeConfig,
        PriceCurrencyInterface     $priceCurrency,
        AllowedOptions             $allowedOptions,
        Json                       $serializer = null,
        UploaderFactory            $uploaderFactory = null
    )
    {
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $storeManager,
            $localeFormat,
            $scopeConfig,
            $priceCurrency,
            $serializer,
            $uploaderFactory
        );
        $this->allowedOptions = $allowedOptions;
    }

    /**
     * Get allowed giftcard amounts
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function _getAllowedAmounts($product)
    {
        $productId = $product->getId();
        if (!isset($this->_giftcardAmounts[$productId])) {
            $allowedAmounts = [];
            foreach ($product->getGiftcardAmounts() as $value) {
                $allowedAmounts[] = $this->priceCurrency->round($value['website_value']);
            }
            /** if allowed amounts is empty in certain cases*/
            if (empty($allowedAmounts)) {
                foreach ($this->allowedOptions->getGiftCardAmounts($product) as $value) {
                    if(isset($value['value'])) {
                        $allowedAmounts[] = $this->priceCurrency->round($value['value']);
                    }
                }
            }
            /** End of allowed amounts is empty in certain cases*/

            $this->_giftcardAmounts[$productId] = $allowedAmounts;
        }
        return $this->_giftcardAmounts[$productId];
    }

}
