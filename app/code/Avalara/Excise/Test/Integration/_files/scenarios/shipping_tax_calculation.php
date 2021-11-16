<?php
/**
 * Avalara_Excise
 *
 */

use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Avalara\Excise\Test\Integration\Model\Tax\Sales\Total\Quote\SetupUtil;

$taxCalculationData['shipping_tax_calculation'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => array_merge($exciseCredentials, [
            'shipping/origin/street_line1' => '4525 Oak St',
            'shipping/origin/city' => 'Kansas City',
            'shipping/origin/region_id' => SetupUtil::REGION_MO,
            'shipping/origin/country_id' => SetupUtil::COUNTRY_US,
            'shipping/origin/postcode' => '64111'
        ])
    ],
    'quote_data' => [
        'billing_address' => [
            'firstname' => 'Jake',
            'lastname' => 'Johnson',
            'street' => '1100 Congress Ave',
            'city' => 'Austin',
            'region_id' => SetupUtil::REGION_TX,
            'postcode' => '78701',
            'country_id' => SetupUtil::COUNTRY_US,
            'telephone' => '999-999-9999'
        ],
        'shipping_address' => [
            'firstname' => 'Jake',
            'lastname' => 'Johnson',
            'street' => '1100 Congress Ave',
            'city' => 'Austin',
            'region_id' => SetupUtil::REGION_TX,
            'postcode' => '78701',
            'country_id' => SetupUtil::COUNTRY_US,
            'telephone' => '999-999-9999'
        ],
        'items' => [
            [
                'type' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'sku' => 'CIGAR',
                'price' => 49.99,
                'qty' => 1
            ]
        ],
        'shipping' => [
            'method' => 'flatrate_flatrate',
            'description' => 'Flat Rate - Fixed',
            'amount' => 5,
            'base_amount' => 5,
        ]
    ],
    'expected_results' => [
        'address_data' => [
            'tax_amount' => 4.53,
            'subtotal' => 49.99,
            'subtotal_incl_tax' => 49.99 + (4.53 - 0.41),
            'grand_total' => 49.99 + 4.53 + 5
        ],
        'items_data' => [
            'CIGAR' => [
                'tax_amount' => 4.12,
                'tax_percent' => 8.250,
                'price' => 49.99,
                'price_incl_tax' => 49.99 + 4.12,
                'row_total' => 49.99,
                'row_total_incl_tax' => 49.99 + 4.12
            ],
            'shipping' => [
                'tax_amount' => 0.41,
                'tax_percent' => 8.250,
                'row_total' => 5,
                'row_total_incl_tax' => 5 + 0.41
            ]
        ],
    ],
];
