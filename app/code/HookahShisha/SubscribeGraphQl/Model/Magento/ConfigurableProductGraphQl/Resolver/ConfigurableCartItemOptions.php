<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Magento\ConfigurableProductGraphQl\Resolver;

use Magento\ConfigurableProductGraphQl\Model\Resolver\ConfigurableCartItemOptions as MageConfigurableCartItemOptions;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;

/**
 * ConfigurableCartItemOptions
 */
class ConfigurableCartItemOptions extends MageConfigurableCartItemOptions
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'configurable';

    /**
     * @var Configuration
     */
    private Configuration $configurationHelper;

    /** @var Uid */
    private Uid $uidEncoder;

    /**
     * @var MetadataPool
     */
    private MetadataPool $metadataPool;

    /**
     * @param Configuration $configurationHelper
     * @param MetadataPool $metadataPool
     * @param Uid $uidEncoder
     */
    public function __construct(
        Configuration $configurationHelper,
        MetadataPool  $metadataPool,
        Uid           $uidEncoder
    ) {
        $this->configurationHelper = $configurationHelper;
        $this->metadataPool = $metadataPool;
        $this->uidEncoder = $uidEncoder;

        parent::__construct(
            $configurationHelper,
            $metadataPool,
            $uidEncoder
        );
    }

    /**
     * Fetch and format configurable variants.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Item $cartItem */
        $cartItem = $value['model'];
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productLinkId = $cartItem->getProduct()->getData($linkField);
        $result = [];
        foreach ($this->configurationHelper->getOptions($cartItem) as $option) {
            if (isset($option['option_type']) || !array_key_exists("option_id", $option)) {
                //Don't return customizable options in this resolver
                continue;
            }
            $result[] = [
                'id' => $option['option_id'],
                'configurable_product_option_uid' => $this->uidEncoder->encode(
                    self::OPTION_TYPE . '/' . $productLinkId . '/' . $option['option_id']
                ),
                'option_label' => $option['label'],
                'value_id' => $option['option_value'],
                'configurable_product_option_value_uid' => $this->uidEncoder->encode(
                    self::OPTION_TYPE . '/' . $option['option_id'] . '/' . $option['option_value']
                ),
                'value_label' => $option['value'],
            ];
        }

        return $result;
    }
}
