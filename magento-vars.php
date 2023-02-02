<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Enable, adjust and copy this code for each store you run
 * Store #0, default one
 * if (isHttpHost("example.com")) {
 *    $_SERVER["MAGE_RUN_CODE"] = "default";
 *    $_SERVER["MAGE_RUN_TYPE"] = "store";
 * }
 * @param string $host
 * @return bool
 */
function isHttpHost(string $host)
{
    if (!isset($_SERVER['HTTP_HOST'])) {
        return false;
    }
    return $_SERVER['HTTP_HOST'] === $host;
}

$_ENV["ACTIVE_STORE"] = "default";

if (isHttpHost("hookah.integration-5ojmyuq-hhdjar4f2w7cu.us-5.magentosite.cloud") || isHttpHost("mcstaging.hookah.com") || isHttpHost("mcprod.hookah.com") || isHttpHost("www.hookah.com") || isHttpHost("hookah.com") || isHttpHost("mcadmin.shisha-world.com") || isHttpHost("mcstaging.shisha-world.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "hookah";
    $_SERVER["MAGE_RUN_TYPE"] = "website";
    $_ENV["ACTIVE_STORE"] = "hookah_store_view";
}

if (isHttpHost("hookah-company.integration-5ojmyuq-hhdjar4f2w7cu.us-5.magentosite.cloud") || isHttpHost("mcstaging.hookah-company.com") || isHttpHost("mcprod.hookah-company.com") || isHttpHost("www.hookah-company.com") || isHttpHost("hookah-company.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "hookah_company";
    $_SERVER["MAGE_RUN_TYPE"] = "website";
    $_ENV["ACTIVE_STORE"] = "hookah_company_store_view";
}

if (isHttpHost("hookahwholesalers.integration-5ojmyuq-hhdjar4f2w7cu.us-5.magentosite.cloud") || isHttpHost("mcstaging.hookahwholesalers.com") || isHttpHost("mcprod.hookahwholesalers.com") || isHttpHost("www.hookahwholesalers.com") || isHttpHost("hookahwholesalers.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "hookah_wholesalers";
    $_SERVER["MAGE_RUN_TYPE"] = "website";
    $_ENV["ACTIVE_STORE"] = "hookah_wholesalers_store_view";
}

if (isHttpHost("b2b.shisha-world.integration-5ojmyuq-hhdjar4f2w7cu.us-5.magentosite.cloud") || isHttpHost("mcstaging.b2b.shisha-world.com") || isHttpHost("mcstaging2.b2b.shisha-world.com") || isHttpHost("mcprod.b2b.shisha-world.com") || isHttpHost("b2b.shisha-world.com")) {
    $_SERVER["MAGE_RUN_CODE"] = "shisha_world_b2b";
    $_SERVER["MAGE_RUN_TYPE"] = "website";
    $_ENV["ACTIVE_STORE"] = "shisha_world_b2b_store_view_de";
}

if (isHttpHost("globalhookah.integration-5ojmyuq-hhdjar4f2w7cu.us-5.magentosite.cloud") || isHttpHost("mcstaging.globalhookah.com") || isHttpHost("mcstaging2.globalhookah.com") || isHttpHost("mcprod.globalhookah.com") || isHttpHost("www.mcprod.globalhookah.com")) {
   $_SERVER["MAGE_RUN_CODE"] = "global_hookah";
    $_SERVER["MAGE_RUN_TYPE"] = "website";
    $_ENV["ACTIVE_STORE"] = "global_hookah_store_view";
}