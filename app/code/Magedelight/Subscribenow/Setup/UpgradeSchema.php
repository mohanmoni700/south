<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private $resourceConnection;
    private $upgrade100x1x1Factory;
    private $upgrade100x1x3Factory;
    private $upgrade100x1x4Factory;
    private $upgrade200x0x0Factory;
    private $upgrade200x0x2Factory;
    private $upgrade200x3x0Factory;
    private $upgrade200x4x0Factory;
    private $upgrade200x5x0Factory;
    private $upgrade200x6x5Factory;

    public function __construct(
        ResourceConnection $resourceConnection,
        Operation\Upgrade\Upgrade100x1x1Factory $upgrade100x1x1Factory,
        Operation\Upgrade\Upgrade100x1x3Factory $upgrade100x1x3Factory,
        Operation\Upgrade\Upgrade100x1x4Factory $upgrade100x1x4Factory,
        Operation\Upgrade\Upgrade200x0x0Factory $upgrade200x0x0Factory,
        Operation\Upgrade\Upgrade200x0x2Factory $upgrade200x0x2Factory,
        Operation\Upgrade\Upgrade200x3x0Factory $upgrade200x3x0Factory,
        Operation\Upgrade\Upgrade200x4x0Factory $upgrade200x4x0Factory,
        Operation\Upgrade\Upgrade200x5x0Factory $upgrade200x5x0Factory,
        Operation\Upgrade\Upgrade200x6x5Factory $upgrade200x6x5Factory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->upgrade100x1x1Factory = $upgrade100x1x1Factory;
        $this->upgrade100x1x3Factory = $upgrade100x1x3Factory;
        $this->upgrade100x1x4Factory = $upgrade100x1x4Factory;
        $this->upgrade200x0x0Factory = $upgrade200x0x0Factory;
        $this->upgrade200x0x2Factory = $upgrade200x0x2Factory;
        $this->upgrade200x3x0Factory = $upgrade200x3x0Factory;
        $this->upgrade200x4x0Factory = $upgrade200x4x0Factory;
        $this->upgrade200x5x0Factory = $upgrade200x5x0Factory;
        $this->upgrade200x6x5Factory = $upgrade200x6x5Factory;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $sales = $this->resourceConnection->getConnection('sales');

        $this->upgrade100x1x1Factory->create()->upgradeSchema($setup, $context, $sales);
        $this->upgrade100x1x3Factory->create()->upgradeSchema($setup, $context, $sales);
        $this->upgrade100x1x4Factory->create()->upgradeSchema($setup, $context, $sales);
        $this->upgrade200x0x0Factory->create()->upgradeSchema($setup, $context, $sales);
        $this->upgrade200x0x2Factory->create()->upgradeSchema($setup, $context, $sales);
        $this->upgrade200x3x0Factory->create()->upgradeSchema($setup, $context, $sales);
        $this->upgrade200x4x0Factory->create()->upgradeSchema($setup, $context, $sales);
        $this->upgrade200x5x0Factory->create()->upgradeSchema($setup, $context, $sales);

        if (version_compare($context->getVersion(), '200.6.5', '<')) {
            $this->upgrade200x6x5Factory->create()->upgradeSchema($setup);
        }

        $setup->endSetup();
    }
}
