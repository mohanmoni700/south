<?php

namespace Alfakher\SalesApprove\Setup\Patch\Data;

/**
 * @author af_bv_op
 */
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SalesApprovedOrderStatusDelete implements DataPatchInterface
{

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('sales_order_status'),
            ['status = ?' => 'sales_approved']
        );

        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('sales_order_status_state'),
            ['status = ?' => 'sales_approved']
        );

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
