<?php

declare(strict_types = 1);

namespace Alfakher\OrderComment\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Add new order statuses with state
 *
 * Class UpdateOrderStatus
 */
class UpdateOrderStatus implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $data = [];
        $statuses = [
            'pending_international_order' => __('Pending International Order'),
            'security_verification_failed' => __('Security Verification Failed'),
            'security_verif_failed_internat' => __('Security Verification Failed International'),
        ];

        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status'),
            ['status', 'label'],
            $data
        );

        $data = [];
        $states = [
            'pending_international_order' => [
                'label' => __('Pending International Order'),
                'state' => 'on_hold',
                'statuses' => ['pending_international_order' => ['default' => '1']],
                'visible_on_front' => true,
            ],
            'security_verification_failed' => [
                'label' => __('Security Verification Failed'),
                'state' => 'on_hold',
                'statuses' => ['security_verification_failed' => ['default' => '1']],
                'visible_on_front' => true,
            ],
            'security_verif_failed_internat' => [
                'label' => __('Security Verification Failed International'),
                'state' => 'on_hold',
                'statuses' => ['security_verif_failed_internat' => ['default' => '1']],
                'visible_on_front' => true,
            ],
        ];
        foreach ($states as $code => $info) {
            if (isset($info['statuses'])) {
                foreach ($info['statuses'] as $status => $statusInfo) {
                    $data[] = [
                        'status' => $status,
                        'state' => $info['state'],
                        'is_default' => is_array($statusInfo) && isset($statusInfo['default']) ? 1 : 0,
                        'visible_on_front' => '1'
                    ];
                }
            }
        }
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default', 'visible_on_front'],
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
