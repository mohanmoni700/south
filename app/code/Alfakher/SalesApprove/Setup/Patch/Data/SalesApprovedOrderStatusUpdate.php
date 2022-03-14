<?php

namespace Alfakher\SalesApprove\Setup\Patch\Data;

/**
 * @author af_bv_op
 */
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SalesApprovedOrderStatusUpdate implements DataPatchInterface
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
        # update order status
        $data = ["visible_on_front" => 1];
        $where = ['status = ?' => "sales_approved"];

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('sales_order_status_state'),
            $data,
            $where
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

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '0.0.2';
    }
}
