<?php

namespace Alfakher\AmastyExtended\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\AbstractItem as NativeAbstractItem;
use Magento\Quote\Model\Quote\Item;

class AbstractItem
{
    /**
     * @var Item
     */
    private $item;

    /**
     * @param NativeAbstractItem $subject
     * @param Item $item
     *
     * @return Item
     */
    public function beforeGetItemData(
        NativeAbstractItem $subject,
        Item               $item
    ) {
        $this->item = $item;
        return [$item];
    }

    /**
     * @param NativeAbstractItem $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetItemData(NativeAbstractItem $subject, $result)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->create(\Psr\Log\LoggerInterface::class);
        $logger->info('my plugin');
        $result['hide_remove_item_in_minicart'] = false;

        if ($this->item->getOptionByCode('amasty_quote_price')) {
            $logger->info('if');
            $result['hide_remove_item_in_minicart'] = true;
        }

        return $result;
    }
}
