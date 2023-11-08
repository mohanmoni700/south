<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\ProductAlert\Block\Email\Stock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ProductAlert\Helper\Data as StoreData;
use Magento\Store\Model\ScopeInterface as ScopeInterface;

class EmailData
{
    public const XML_PATH_EMAIL_STOCK_TEMPLATE = 'catalog/productalert/guest_user_email_template';

    public const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_general/email';

    /**
     * Product collection which of back in stock
     *
     * @var array
     */
    protected $_stockProducts = [];

    /**
     * @var Stock
     */
    protected $_stockBlock;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @var StoreData
     */
    protected $productAlertData = null;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var State
     */
    private State $appState;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreData $productAlertData
     * @param State $appState
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreData $productAlertData,
        State $appState
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productAlertData = $productAlertData;
        $this->appState = $appState;
    }

    /**
     * Retrieve stock block
     *
     * @return Stock
     * @throws LocalizedException
     */
    public function getStockBlock()
    {
        if ($this->_stockBlock === null) {
            $this->_stockBlock = $this->productAlertData->createBlock(Stock::class);
        }
        return $this->_stockBlock;
    }

    /**
     * To get the Alert grid
     *
     * @param string $store
     * @param $product
     * @return mixed
     */
    public function getAlertGrid($store, $product)
    {
        $this->addStockProduct($product);
        $block = $this->getStockBlock();
        $block->setStore($store)->reset();
        $block->addProduct($product);

        return $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$block, 'toHtml']
        );
    }

    /**
     * To get Email sender data
     *
     * @param $storeId
     * @return mixed
     */
    public function getSender($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * To get email template id
     *
     * @param $storeId
     * @return mixed
     */
    public function getTemplateId($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_STOCK_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Add product (back in stock) to collection
     *
     * @param Product $product
     *
     * @return $this
     */
    public function addStockProduct(Product $product)
    {
        $this->_stockProducts[$product->getId()] = $product;
        return $this;
    }
}
