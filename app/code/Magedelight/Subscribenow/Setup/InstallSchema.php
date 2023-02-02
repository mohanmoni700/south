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
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    private $productSubscribersFactory;
    private $associateOrdersFactory;
    private $subscriptionHistoryFactory;
    private $aggregatedCustomerFactory;
    private $aggregatedProductFactory;
    private $addCustomExtensionAttribute;
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        Operation\Create\ProductSubscribersFactory $productSubscribersFactory,
        Operation\Create\AssociateOrdersFactory $associateOrdersFactory,
        Operation\Create\SubscriptionHistoryFactory $subscriptionHistoryFactory,
        Operation\Create\AggregatedCustomerFactory $aggregatedCustomerFactory,
        Operation\Create\AggregatedProductFactory $aggregatedProductFactory,
        Operation\AddCustomExtensionAttribute $addCustomExtensionAttribute
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productSubscribersFactory = $productSubscribersFactory;
        $this->associateOrdersFactory = $associateOrdersFactory;
        $this->subscriptionHistoryFactory = $subscriptionHistoryFactory;
        $this->aggregatedCustomerFactory = $aggregatedCustomerFactory;
        $this->aggregatedProductFactory = $aggregatedProductFactory;
        $this->addCustomExtensionAttribute = $addCustomExtensionAttribute;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $sales = $this->resourceConnection->getConnection('sales');
        $checkout = $this->resourceConnection->getConnection('checkout');

        $this->productSubscribersFactory->create()->execute($setup, $sales);
        $this->associateOrdersFactory->create()->execute($setup, $sales);
        $this->subscriptionHistoryFactory->create()->execute($setup, $sales);
        $this->aggregatedCustomerFactory->create()->execute($setup, $sales);
        $this->aggregatedProductFactory->create()->execute($setup, $sales);
        $this->addCustomExtensionAttribute->execute($setup, $sales, $checkout);

        $installer->endSetup();
    }
}
