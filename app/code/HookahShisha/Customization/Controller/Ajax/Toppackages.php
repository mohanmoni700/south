<?php

namespace Brainvire\Customization\Controller\Ajax;

/**
 *
 */
class Toppackages extends \Magento\Framework\App\Action\Action
{

    const TOP_SPORTS_PACKAGES = 'home_setting/home/top_sports_packages';

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Helper\Image $productImageHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Catalog\Helper\Product\Compare $compareHelper
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_productCollection = $productCollection;
        $this->_productImageHelper = $productImageHelper;
        $this->_wishlistHelper = $wishlistHelper;
        $this->_compareHelper = $compareHelper;

        parent::__construct($context);
    }

    public function execute()
    {

        $post = $this->getRequest()->getParams();

        if (!isset($post['page'])) {
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        }

        $proSkus = $this->_scopeConfig->getValue(self::TOP_SPORTS_PACKAGES);
        $proSkus = explode(',', $proSkus);
        $itemArr = [];

        if (count($proSkus)) {
            $bvArray = array_chunk($proSkus, 4);
            // $result['page_count'] = count($bvArray);

            $collection = $this->_productCollection->create();
            $collection->addattributetoselect(['name', 'small_image', 'thumbnail', 'image']);
            $collection->addAttributeToFilter('sku', ['in' => $bvArray[$post['page'] - 1]]);

            foreach ($collection as $product) {
                $itemArr[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'url' => $product->getProductUrl(),
                    'img' => $this->_productImageHelper->init($product, 'product_page_image_large')->getUrl(),
                    'wishlist' => $this->_wishlistHelper->getAddParams($product),
                    'compare' => $this->_compareHelper->getPostDataParams($product),
                ];
            }
        }

        $result = $this->_resultJsonFactory->create();
        $result->setData($itemArr);
        return $result;
    }
}
