<?php
declare (strict_types = 1);

namespace Alfakher\KlaviyoCustomCatalog\Model;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\AbstractModel;

class KlaviyoCustomCatalog extends AbstractModel
{

    public const ADMIN_PRODUCT = 'WS-BTO-AdminOnly';

    /**
     * Kleviyo feed store ids config path
     */
    public const KLAVIYO_CATALOG_STORES = 'hookahshisha/kalviyo_customcatalog/store_ids';

    /**
     * KlaviyoCustomCatalog constructor
     *
     * @param \Magento\Framework\App\State                       $state
     * @param \Magento\Framework\Api\SearchCriteriaBuilder       $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface    $productsInterface
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct
     * @param \Magento\Catalog\Helper\Product                    $imageHelper
     * @param \Magento\Framework\Filesystem\Io\File              $file
     * @param \Magento\Framework\Json\EncoderInterface           $jsonEncoder
     * @param \Magento\Framework\App\Response\Http\FileFactory   $fileFactory
     * @param \Magento\Framework\Filesystem                      $filesystem
     * @param \Magento\Store\Model\App\Emulation                 $emulation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productsInterface,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct,
        \Magento\Catalog\Helper\Product $imageHelper,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_state = $state;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_productsInterface = $productsInterface;
        $this->_groupedProduct = $groupedProduct;
        $this->_imageHelper = $imageHelper;
        $this->file = $file;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_fileFactory = $fileFactory;
        $this->_filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
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

            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter('store_id', $storeId, 'eq')
                ->addFilter('status', Status::STATUS_ENABLED, 'eq')
                ->create();

            $list = $this->_productsInterface->getList($searchCriteria)->getItems();

            foreach ($list as $key => $pro) {
                $productUrl = $pro->getUrlKey();
                $parentProducts = $this->_groupedProduct->getParentIdsByChild($pro->getId());

                if (count($parentProducts) > 0) {
                    try {
                        $groupProduct = $this->_productsInterface->getById($parentProducts[0]);
                        $productUrl = $groupProduct->getUrlKey();

                        if ($groupProduct->getSku() == self::ADMIN_PRODUCT && isset($parentProducts[1])) {
                            $groupProductNext = $this->_productsInterface->getById($parentProducts[0]);
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
                    "image_link" => $this->_imageHelper->getThumbnailUrl($pro) ?: "unavailable",
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
     * @param  array $productArray
     * @param  int $storeId
     * @return boolean
     */
    public function generateFeedJsonFile($productArray, $storeId)
    {
        $filePrefix = 'klaviyocustomcatalog';
        $extension = '.json';
        $fileName = $filePrefix . '_' . $storeId . $extension;

        $result = false;
        try {
            $content = $this->_jsonEncoder->encode($productArray);
            $media = $this->_filesystem->getDirectoryWrite(DirectoryList::PUB);
            $media->writeFile($fileName, $content);
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}
