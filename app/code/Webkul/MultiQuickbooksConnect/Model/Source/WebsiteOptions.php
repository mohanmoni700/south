<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WebsiteOptions implements OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;

    /**
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     */
    public function __construct(
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\System\Store $systemStore
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->systemStore = $systemStore;
    }

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $websites = [];
        // $websites = $this->websiteFactory->create()->getCollection()->toOptionArray();
        $websites = $this->systemStore->getWebsiteValuesForForm();
        return $websites;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $optionList = $this->toOptionArray();
        $optionArray=[];
        foreach ($optionList as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }
}
