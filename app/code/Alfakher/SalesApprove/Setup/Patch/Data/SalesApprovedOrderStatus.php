<?php

namespace Alfakher\SalesApprove\Setup\Patch\Data;

/**
 * @author af_bv_op
 */
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SalesApprovedOrderStatus implements DataPatchInterface
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
        # create new order status
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('sales_order_status'),
            ['status' => 'sales_approved', 'label' => 'Sales Approved']
        );

        # Bind status to state
        $states = [
            [
                'status' => 'sales_approved',
                'state' => 'processing',
                'is_default' => 0,
            ],
        ];
        foreach ($states as $state) {
            $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                $this->moduleDataSetup->getTable('sales_order_status_state'),
                $state
            );
        }
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
