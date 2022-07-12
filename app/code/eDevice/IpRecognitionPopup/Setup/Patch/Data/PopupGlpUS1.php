<?php
/**
 * @category  eDevice
 * @package   eDevice_IpRecognitionPopup
 * @author    CORRA
 */
declare (strict_types = 1);

namespace eDevice\IpRecognitionPopup\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Store switching popup on GLP for US users
 */
class PopupGlpUS1 implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $stores = $this->storeManager->getStores();
        $storeCode = 'ooka_store_view';
        $storeId = 0;

        if(isset($stores[$storeCode])) {
            $storeId = $stores[$storeCode]->getId();
        }

        $cmsBlockData = [
            'title' => 'Store switching popup on GLP for US users',
            'identifier' => 'store-switching-popup-ooka_store_view_US',
            'content' => '
                <div style="text-align: center;">
                    <svg class="location-icon" style="margin-left: 50%;" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.9999 3.625C18.0601 3.6251 16.1556 4.14406 14.4839 5.12807C12.8122 6.11208 11.4341 7.52533 10.4925 9.2213C9.55096 10.9173 9.08015 12.8342 9.12893 14.7734C9.1777 16.7126 9.74428 18.6035 10.7699 20.25L19.9999 33.75L29.2299 20.25C30.2556 18.6035 30.8222 16.7126 30.8709 14.7734C30.9197 12.8342 30.4489 10.9173 29.5073 9.2213C28.5657 7.52533 27.1877 6.11208 25.516 5.12807C23.8442 4.14406 21.9398 3.6251 19.9999 3.625ZM19.9999 18.5C19.2088 18.5 18.4355 18.2654 17.7777 17.8259C17.1199 17.3864 16.6072 16.7616 16.3044 16.0307C16.0017 15.2998 15.9225 14.4956 16.0768 13.7196C16.2311 12.9437 16.6121 12.231 17.1715 11.6716C17.7309 11.1122 18.4437 10.7312 19.2196 10.5769C19.9955 10.4225 20.7998 10.5017 21.5307 10.8045C22.2616 11.1072 22.8863 11.6199 23.3258 12.2777C23.7653 12.9355 23.9999 13.7089 23.9999 14.5C23.9999 15.5609 23.5785 16.5783 22.8284 17.3284C22.0782 18.0786 21.0608 18.5 19.9999 18.5Z" fill="#080807"/>
                    <ellipse cx="20" cy="37" rx="12" ry="2" fill="#080807"/>
                    </svg>
                    <p>We noticed you are from United States. Would you like to switch to the local site?</p>
                    <a href="https://usa.ooka.com/en/">Visit United States site</a>
                    <button id="store-switcher-popup-close">No, thanks</button>
                </div>
            ',
            'is_active' => 1,
            'stores' => [$storeId],
            'sort_order' => 0
        ];

        $this->blockFactory->create()->setData($cmsBlockData)->save();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
