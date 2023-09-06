<?php

namespace Alfakher\StockAlert\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

class Data
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param RequestInterface $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Registry $registry,
        RequestInterface $request
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->request = $request;
    }

    /**
     * Get store ID
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get Website ID
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getWebsiteId(): int
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Get Product ID
     *
     * @return mixed
     */
    public function getProductId(): mixed
    {
        return $this->request->getParam('product_id');
    }
}
