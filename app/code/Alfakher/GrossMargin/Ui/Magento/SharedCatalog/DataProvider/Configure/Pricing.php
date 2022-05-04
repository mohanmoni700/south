<?php

namespace Alfakher\GrossMargin\Ui\Magento\SharedCatalog\DataProvider\Configure;

/**
 * @author af_bv_op
 */
use Magento\SharedCatalog\Model\Form\Storage\WizardFactory as WizardStorageFactory;

class Pricing extends \Magento\SharedCatalog\Ui\DataProvider\Configure\Pricing
{
    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor
     */
    private $stepDataProcessor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\App\RequestInterface $request
     * @param WizardStorageFactory $wizardStorageFactory
     * @param \Magento\SharedCatalog\Model\ResourceModel\CategoryTree $categoryTree
     * @param \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor $stepDataProcessor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param array $meta [optional]
     * @param array $data [optional]
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\App\RequestInterface $request,
        WizardStorageFactory $wizardStorageFactory,
        \Magento\SharedCatalog\Model\ResourceModel\CategoryTree $categoryTree,
        \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor $stepDataProcessor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $request,
            $wizardStorageFactory,
            $categoryTree,
            $stepDataProcessor,
            $storeManager,
            $meta,
            $data
        );
        $this->stepDataProcessor = $stepDataProcessor;

        $this->oprequest = $request;
        $this->_productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject
     */
    protected function prepareDataItem(\Magento\Framework\DataObject $item)
    {
        $customPrices = $this->getStorage()->getProductPrices($item->getSku());
        $customPrice = $this->stepDataProcessor->prepareCustomPrice($customPrices);
        $priceType = \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface::VALUE_FIXED;
        if (is_array($customPrice) && !empty($customPrice)) {
            $priceType = $customPrice['value_type'];
            if ($priceType == \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED) {
                $item->setCustomPrice($customPrice['price']);
                $priceType = \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface::VALUE_FIXED;
            } else {
                $item->setCustomPrice($customPrice['percentage_value']);
                $priceType = \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface::VALUE_PERCENT;
            }
        }
        $item->setPriceType($priceType);
        $item->setOriginPrice($item->getPrice());

        $tierPrices = $this->getStorage()->getTierPrices($item->getSku());
        $item->setData('tier_price_count', count($tierPrices));
        $item->setData('custom_price_enabled', $this->stepDataProcessor->isCustomPriceEnabled($customPrices));
        $item->setData('gross_margin', $this->calculateGrossMargin($item));

        return parent::prepareDataItem($item);
    }

    /**
     * Calculate Gross Margin
     *
     * @param \Magento\Framework\DataObject $item
     */
    protected function calculateGrossMargin($item)
    {
        $itemData = $item->getData();
        $requestParams = $this->oprequest->getParams();
        if (isset($itemData['custom_price']) && isset($requestParams['isAjax']) && $requestParams['isAjax'] == true) {
            $cost = $itemData['cost'] ? $itemData['cost'] : 0;
            $price = $itemData['price'] ? $itemData['price'] : 0;

            if ($itemData['price_type'] == 'percent') {
                $discountValue = $price * ($itemData['custom_price'] / 100);
                $price = $price - $discountValue;
            } else {
                $price = $itemData['custom_price'];
            }

            try {
                $grossMargin = ($price - $cost) / $price * 100;
                return number_format($grossMargin, 2) . "%";
            } catch (\Exception $e) {
                return "0.00%";
            }

        } else {
            $cost = 0;
            $price = 0;
            $product = $this->_productRepository->get($item->getSku());

            if ($product->getCost() > -1) {
                $cost = $product->getCost();
            }

            if ($product->getPrice() > -1) {
                $price = $product->getPrice();
            }

            try {
                $grossMargin = ($price - $cost) / $price * 100;
                return number_format($grossMargin, 2) . "%";
            } catch (\Exception $e) {
                return "0.00%";
            }
        }
    }
}
