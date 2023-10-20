<?php

declare(strict_types=1);

namespace Corra\AmastyPromoGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Amasty\Promo\Helper\Data as PromoHelper;
use Corra\AmastyPromoGraphQl\Plugin\Data as PluginData;
use Amasty\Promo\Helper\Item as ItemData;

/**
 * Resolver for fetching free gifts to cart
 */
class GetFreeGiftPromoItems implements ResolverInterface
{
    /**
     * @var PromoHelper
     */
    private $promoHelper;

    /**
     * @var PluginData
     */
    private $pluginData;

    /**
     * @var ItemData
     */
    private $itemData;

    /**
     * GetFreeGiftPromoItems constructor.
     * @param PromoHelper $promoHelper
     * @param PluginData $pluginData
     * @param ItemData $itemData
     */
    public function __construct(
        PromoHelper $promoHelper,
        PluginData $pluginData,
        ItemData $itemData
    ) {
        $this->promoHelper = $promoHelper;
        $this->pluginData = $pluginData;
        $this->itemData = $itemData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cart = $value['model'];
        $promoGiftItems = [];
        $promoSampleItems = [];
        $cartAppliedRules = $cart->getAppliedRuleIds();
        if ($cartAppliedRules) {
            $cartItems = $cart->getAllItems();
            $this->pluginData->setRuleData($cartAppliedRules);
            $this->pluginData->setSubtotalValue($cartItems);
            $promoItems = $this->promoHelper->getPromoItemsDataArray($cart);
            $gifts = $promoItems['free_gifts'];
            array_multisort(array_column($gifts, 'sku'), SORT_ASC, $gifts);
            $samples = $promoItems['free_samples'];
            array_multisort(array_column($samples, 'sku'), SORT_ASC, $samples);
            $giftsUpdated = $gifts;
            $samplesUpdated = $samples;
            foreach ($cartItems as $cartItem) {
                $amPromoRuleId = ($cartItem->getData('ampromo_rule_id')) ?? $this->itemData->getRuleId($cartItem);

                if ((isset($amPromoRuleId) && $amPromoRuleId) && 0 == $cartItem->getPrice()) {
                    $gifts = $this->getGiftsUpdated($gifts, $amPromoRuleId, $cartItem);
                    $samplesUpdated = $this->getSamplesUpdated($samples, $amPromoRuleId, $cartItem);
                }
            }
            $promoGiftItems = $giftsUpdated;
            $promoSampleItems = $samplesUpdated;
        }
        return [
            'free_gifts' => $promoGiftItems,
            'free_samples' => $promoSampleItems
        ];
    }

    /**
     * @param $gifts
     * @param $amPromoRuleId
     * @param $cartItem
     * @return array
     */
    private function getGiftsUpdated($gifts, $amPromoRuleId, $cartItem)
    {
        $giftsUpdated = [];
        foreach ($gifts as $gift) {
            if ($gift['rule_id'] == $amPromoRuleId) {
                $qty = $gift['max_qty'] - $cartItem->getQty();
                $gift['max_qty'] = $qty;
                if ($gift['sku'] == $cartItem->getSku()) {
                    $gift['is_added'] = 1;
                }
            }
            array_push($giftsUpdated, $gift);
        }
        return $giftsUpdated;
    }

    /**
     * @param $samples
     * @param $amPromoRuleId
     * @param $cartItem
     * @return array
     */
    private function getSamplesUpdated($samples, $amPromoRuleId, $cartItem)
    {
        $samplesUpdated = [];
        foreach ($samples as $sample) {
            if ($sample['rule_id'] == $amPromoRuleId) {
                $qty = $sample['max_qty'] - $cartItem->getQty();
                $sample['max_qty'] = $qty;
                if ($sample['sku'] == $cartItem->getSku()) {
                    $sample['is_added'] = 1;
                }
            }
            array_push($samplesUpdated, $sample);
        }
        return $samplesUpdated;
    }
}
