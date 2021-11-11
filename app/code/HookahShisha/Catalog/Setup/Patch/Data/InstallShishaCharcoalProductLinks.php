<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Catalog
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Catalog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use HookahShisha\Catalog\Model\Product\Link;

/**
 * Class InstallShishaCharcoalProductLinks for installing Shisha and Charcoal product types
 */
class InstallShishaCharcoalProductLinks implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /**
         * Install product link types
         */
        $data = [
            ['link_type_id' => Link::LINK_TYPE_SHISHA, 'code' => 'shisha'],
            ['link_type_id' => Link::LINK_TYPE_CHARCOAL, 'code' => 'charcoal']
        ];

        foreach ($data as $link) {
            $this->moduleDataSetup->getConnection()->insertForce(
                $this->moduleDataSetup->getTable(
                    'catalog_product_link_type'
                ),
                $link
            );
        }

        /**
         * install product link attributes
         */
        $data = [
            [
                'link_type_id' => Link::LINK_TYPE_SHISHA,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int',
            ],
            [
                'link_type_id' => Link::LINK_TYPE_CHARCOAL,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int'
            ]
        ];

        $this->moduleDataSetup->getConnection()->insertMultiple(
            $this->moduleDataSetup->getTable('catalog_product_link_attribute'),
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
