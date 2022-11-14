<?php
declare(strict_types=1);

namespace Alfakher\KlaviyoCustomCatalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\App\Emulation;

class KlaviyoCustomCatalog extends AbstractModel
{
    /**
     * Admin product sku
     */
    public const ADMIN_PRODUCT = 'WS-BTO-AdminOnly';

    /**
     * Kleviyo feed store ids config path
     */
    public const KLAVIYO_CATALOG_STORES = 'hookahshisha/kalviyo_customcatalog/store_ids';

    /**
     * @var State
     */
    protected $state;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productsInterface;

    /**
     * @var Grouped
     */
    protected $groupedProduct;

    /**
     * @var Product
     */
    protected $imageHelper;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * KlaviyoCustomCatalog constructor
     *
     * @param State $state
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productsInterface
     * @param Grouped $groupedProduct
     * @param Product $imageHelper
     * @param File $file
     * @param EncoderInterface $jsonEncoder
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param Emulation $emulation
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        State $state,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productsInterface,
        Grouped $groupedProduct,
        Product $imageHelper,
        File $file,
        EncoderInterface $jsonEncoder,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        Emulation $emulation,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->state = $state;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productsInterface = $productsInterface;
        $this->groupedProduct = $groupedProduct;
        $this->imageHelper = $imageHelper;
        $this->file = $file;
        $this->jsonEncoder = $jsonEncoder;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->emulation = $emulation;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Generated latest feed for klaviyo custom catalog feed sync
     *
     * @return void
     */
    public function generateKlaviyoCustomCatalogFeed()
    {
        $storeIdsConfig = $this->scopeConfig->getValue(self::KLAVIYO_CATALOG_STORES);
        $storeIds = explode(',', $storeIdsConfig);

        foreach ($storeIds as $storeId) {
            $productArray = [];
            $this->emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND);
            
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('store_id', $storeId, 'eq')
                ->addFilter('status', Status::STATUS_ENABLED, 'eq')
                ->create();
            $list = $this->productsInterface->getList($searchCriteria)->getItems();

            foreach ($list as $key => $pro) {
                $productUrl = $pro->getUrlKey();
                $parentProducts = $this->groupedProduct->getParentIdsByChild($pro->getId());
                if (count($parentProducts) > 0) {
                    try {
                        $groupProduct = $this->productsInterface->getById($parentProducts[0]);
                        $productUrl = $groupProduct->getUrlKey();
                        if ($groupProduct->getSku() == self::ADMIN_PRODUCT && isset($parentProducts[1])) {
                            $groupProductNext = $this->productsInterface->getById($parentProducts[0]);
                            $productUrl = $groupProductNext->getUrlKey();
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                $productArray[] = [
                    "id" => $pro->getId(),
                    "title" => $pro->getName(),
                    "link" => $pro->getProductUrl(),
                    "description" => $pro->getShortDescription() ? $pro->getShortDescription() : "unavailable",
                    "image_link" => $this->imageHelper->getThumbnailUrl($pro) ?: "unavailable",
                    "b2b_url_key" => $productUrl,
                ];
            }

            $feed = $this->generateFeedJsonFile($productArray, $storeId);
            $this->emulation->stopEnvironmentEmulation();
        }
    }

    /**
     * Generated klaviyo custom catalog's json file from given product array
     *
     * @param array $productArray
     * @param int $storeId
     * @return boolean
     */
    public function generateFeedJsonFile($productArray, $storeId)
    {
        $filePrefix = 'klaviyocustomcatalog';
        $extension = '.json';
        $fileName = $filePrefix . '_' . $storeId . $extension;
        try {
            $content = $this->jsonEncoder->encode($productArray);
            $media = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $media->writeFile($fileName, $content);
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}
