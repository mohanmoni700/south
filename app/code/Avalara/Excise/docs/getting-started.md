# Documentation

- [Home](../README.md)
- [Getting Started](./getting-started.md)
- Extension Features
  - [Excise Sales Tax](./excise-sales-tax.md)
  - [Address Validation](./address-validation.md)

# Getting Started

## Table of Contents

- [Getting Started](#getting-started)
  * [Support](#support)
  * [Prerequisites](#prerequisites)
  * [Supported Magento Versions](#supported-magento-versions)
  * [Installation](#installation)
    + [Install via Composer](#install-via-composer)
  * [Configuration](#configuration)
- [Release Notes](#release-notes)
- [Pre-Launch Sales Record Cleanup](#pre-launch-sales-record-cleanup)
- [Uninstall Extension](#uninstall-extension)

## Getting Started

### Support

For support with your AvaTax account, please visit [avalara.com/technical-support](http://www.avalara.com/Technical-Support). This software will not work unless you have a valid Excise and Avatax account. To obtain the required account information, please contact your Avalara representative.

### Prerequisites

- Active Excise account with a company setup.
- Active Avatax account with a company setup.
- Magento running on a server that has the following:
  - Uninstall avalara/avatax-magento extension, if exists.
  - Properly configured CRON job
  - PHP CURL extensions (required by the AvaTax/Excise library)

### Supported Magento Versions

supported Magento editions/versions.

- Magento 2.4.0 - 2.4.2 (as of Avalara_Excise 1.0.0)
  - :white_check_mark: Open Source (Community)
  - :white_check_mark: Commerce (Enterprise)
  - :white_check_mark: Commerce Cloud
- Magento 2.3.x (as of Avalara_Excise 1.0.0)
  - :white_check_mark: Open Source (Community)
  - :white_check_mark: Commerce (Enterprise)
  - :white_check_mark: Commerce Cloud
- Magento 2.2.x (as of Avalara_Excise 1.0.0)
  - :white_check_mark: Open Source (Community)
  - :white_check_mark: Commerce (Enterprise)
  - :white_check_mark: Commerce Cloud

###  Installation

#### Install via Composer

This is the recommended installation method as it allows you to easily update the extension in the future. **Important:** Installation must be performed by an experienced Magento developer and these instructions assume that is the case. Installation support can only be provided to developers.

1. Require the desired version of Avalara Excise/Avatax. Latest version can be installed by running following command:

   ```
   composer require avalara/excise
   ```

2. Setup the AvaTax module in magento

   ```bash
   bin/magento module:enable --clear-static-content Avalara_Excise
   bin/magento setup:upgrade
   bin/magento cache:flush
   ```

3. If you are deploying the extension to a production environment, follow the [devdocs.magento.com deployment instructions](http://devdocs.magento.com/guides/v2.0/howdoi/deploy/deploy-to-prod.html#deploy-prod)

### Configuration

1. To configure the extension, go to `Stores > Settings > Configuration > Sales > Tax.` 
2. Details on configuring each of the extension features:
  - [Avatax Excise - General](./excise-sales-tax.md#configuration)
  - [Avatax - Address Validation](./address-validation.md#configuration)
3. In the Magento admin, go to `Stores > Settings > Configuration > Sales > Shipping Settings`. Click on the **Origin** section. Enter the address that will be used as the origin or **Shipped From** location in Avalara's Excise tax calculation. **It is *required*** that you specify a postal code in the origin address, no matter what country is specified. Otherwise, you will get errors in checkout and when saving customers.

## Release Notes

Extension Features
    1. Excise tax calculation.
    2. Sales tax calculation.
    3. Shipping and billing address validation.
    4. Submitting invoice and credit memo transactions to Avatax.
    5. Application logger for troubleshooting.


## Uninstall Extension

1. Run this command in the root of your Magento installation directory: `bin/magento module:uninstall Avalara_Excise`

2. If you installed the module using Composer, run these commands in the root of your Magento installation directory:

   ```bash
   composer remove avalara/excise
   ```

   If you installed the module by copying files, run these commands in the root of your Magento installation directory:

   ```bash
   rm -rf app/code/Avalara/Excise
   ```

3. Run the following queries in your Magento database:

    ```bash
   -- Remove Excise tables (these tables will be in the sales database in split-database mode)
   DROP TABLE `excise_queue`;
   DROP TABLE `excise_log`;
   ```