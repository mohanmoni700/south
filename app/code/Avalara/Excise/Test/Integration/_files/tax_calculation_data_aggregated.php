<?php
/**
 * Avalara_Excise
 *
 */

$exciseCredentials = require __DIR__ . '/../credentials.php';

/**
 * Global array that holds test scenarios data
 *
 * @var array
 */
$taxCalculationData = [];

require_once __DIR__ . '/scenarios/products/configurable_product.php';
require_once __DIR__ . '/scenarios/products/simple_product.php';
require_once __DIR__ . '/scenarios/shipping_tax_calculation.php';
