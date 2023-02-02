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

use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $appState;
    private $addProductAttribute;
    private $upgrade100x1x3Factory;
    private $upgrade200x5x0Factory;
    private $columnDataUpgradeFactory;
    private $mysqlCompatibilityUpgradeFactory;
    private $resourceConnection;
    private $upgrade200x5x2Factory;
    private $upgrade200x6x5Factory;

    public function __construct(
        State $appState,
        ResourceConnection $resourceConnection,
        Operation\AddProductAttribute $addProductAttribute,
        Operation\Upgrade\Upgrade100x1x3Factory $upgrade100x1x3Factory,
        Operation\Upgrade\Upgrade200x5x0Factory $upgrade200x5x0Factory,
        Operation\Upgrade\ColumnDataUpgradeFactory $columnDataUpgrade,
        Operation\Upgrade\MysqlCompatibilityUpgradeFactory $mysqlCompatibilityUpgrade,
        Operation\Upgrade\Upgrade200x5x2Factory $upgrade200x5x2Factory,
        Operation\Upgrade\Upgrade200x6x5Factory $upgrade200x6x5Factory
    ) {
        $this->appState = $appState;
        $this->resourceConnection = $resourceConnection;
        $this->addProductAttribute = $addProductAttribute;
        $this->columnDataUpgradeFactory = $columnDataUpgrade;
        $this->mysqlCompatibilityUpgradeFactory = $mysqlCompatibilityUpgrade;
        $this->upgrade100x1x3Factory = $upgrade100x1x3Factory;
        $this->upgrade200x5x0Factory = $upgrade200x5x0Factory;
        $this->upgrade200x5x2Factory = $upgrade200x5x2Factory;
        $this->upgrade200x6x5Factory = $upgrade200x6x5Factory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        try {
            $this->appState->setAreaCode(Area::AREA_GLOBAL);
        } catch (\Exception $e) {
        }

        $sales = $this->resourceConnection->getConnection('sales');

        $this->addProductAttribute->execute($setup);
        $this->upgrade100x1x3Factory->create()->upgradeData($setup, $context);

        if (version_compare($context->getVersion(), '200.0.2', '<')) {
            $this->columnDataUpgradeFactory->create()->upgradeData($setup, $sales);
        }

        if (version_compare($context->getVersion(), '200.4.0', '<')) {
            $this->mysqlCompatibilityUpgradeFactory->create()->upgradeData($setup, $sales);
        }

        if (version_compare($context->getVersion(), '200.5.0', '<')) {
            $this->upgrade200x5x0Factory->create()->upgradeData($setup, $context, $sales);
        }

        if (version_compare($context->getVersion(), '200.5.2', '<')) {
            $this->upgrade200x5x2Factory->create()->upgradeData($setup, $context, $sales);
        }

        if (version_compare($context->getVersion(), '200.6.5', '<')) {
            $this->upgrade200x6x5Factory->create()->upgradeData($setup);
        }

        $setup->endSetup();
    }
}
