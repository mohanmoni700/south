<?php

namespace Alfakher\KlaviyoCustomCatalog\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    public const ADMIN_PRODUCT = 'WS-BTO-AdminOnly';

    /**
     * Result page factory variable
     *
     * @var $_pageFactory
     */
    protected $_pageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productsInterface
     * @param \Magento\Catalog\Helper\Product $imageHelper
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productsInterface,
        \Magento\Catalog\Helper\Product $imageHelper,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;

        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_productsInterface = $productsInterface;
        $this->_imageHelper = $imageHelper;
        $this->_groupedProduct = $groupedProduct;
        $this->_resultJsonFactory = $resultJsonFactory;

        return parent::__construct($context);
    }

    /**
     * Execute method
     */
    public function execute()
    {
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $list = $this->_productsInterface->getList($searchCriteria)->getItems();

        $productArray = [];
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

        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData($productArray);
    }
}
