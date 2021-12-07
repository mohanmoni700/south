<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_RmaGraphQl
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\RmaGraphQl\Model\Resolver\CustomerOrder\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Rma\Helper\Data as RmaHelper;
use Magento\RmaGraphQl\Model\Resolver\CustomerOrder\Item\IsEligible as SourceIsEligible;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolver for eligible_for_return flag
 */
class IsEligible extends SourceIsEligible
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var RmaHelper
     */
    private RmaHelper $helper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param RmaHelper $helper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        RmaHelper $helper
    ) {
        parent::__construct($productRepository, $storeManager, $helper);

        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model']) && !($value['model'] instanceof OrderItemInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var OrderItemInterface $order */
        $orderItem = $value['model'];

        $storeId = $this->storeManager->getStore()->getId();
        $product = $orderItem->getProduct()
            ? $this->productRepository->getById($orderItem->getProductId()) : false;

        if (!$product) {
            return false;
        }

        return $this->helper->canReturnProduct($product, $storeId);
    }
}
