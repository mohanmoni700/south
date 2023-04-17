# Documentation

- [Home](../README.md)
- [Getting Started](./getting-started.md)
- Extension Features
  - [Excise and Sales Tax](./excise-sales-tax.md)
  - [Address Validation](./address-validation.md)

# Excise and Sales Tax

## Table of Contents

- [Overview](#overview)
- [Configuration](#configuration)
- [Product Custom attributes](#product-attributes)
- [Tax Breakup](#tax-breakup)
- [AvaTax Queue](#avatax-queue)
- [AvaTax Logging](#avatax-logging)
- [Magento Order and Invoice Numbers](#magento-order-and-invoice-numbers)

## Overview

Tax calculation typically happened during checkout process like shopping cart, checkout. This extension allows magento to calculate excise tax, sales tax and address validation. Extension will calculate tax as soon as customers enter postal code either **Estimate Shipping and Tax** on shopping cart or **Shipping Address** form during the checkout process. Tax will be calculated by calling Excise API. The API will identify the products based on the product configuration and will calculate the applicable excise or sales tax.

To use excise API for tax calculation, make sure you set [Product Custom attributes](#product-attributes) values.

A cronjob task runs every five minutes to send invoices and credit memos to Avalara Excise. The status of each pending item can be found in the AvaTax Queue in `Stores > AvaTax Queue`. The Magento CRON must be configured for the extension to work properly.

## Configuration

1. In the Magento admin, go to `Stores > Settings > Configuration > Sales > Tax`. Click on the **Avatax Excise - General** section.
2. Review each of the options in this section and input the appropriate value. This is [a screenshot of the configuration options.](images/Configuration__Settings__Stores__Magento_Admin.png?raw=true)
3. The comment text underneath each of the options in this section should explain the purpose of the setting.

## Product Attributes
Custom product attributes are defined on `Catalog > Products`. Custom attributes are defined under "Excise Attributes" tab.

**Excise Product Code** - This value is tax class configured in Avatax. If this is set as none then product SKU will be sent instead in the API call.

**Unit Of Measure** - The Measurement type, the measure value in which the product is defined.

**Unit Quantity Unit Of Measure** - Product unit quantity of measure.

**Unit Volume Unit Of Measure** - Product volume unit of measure.

**Purchase Unit Price** - The purchase price per unit.

**Purchase Line Amount** - The purchase line amount.

**Unit Quantity** - Product unit quantity.

**Alternative Product Content** - This field is required for products having alternate fuel content.

**Unit Volume** - Volume of a unit.

![](images/product-attributes.png?raw=true)


## Tax Breakup

The module will provide the tax break up on the order, invoice and credit memo detail page in the Magento backend. This will enable the administrator to see the components that are part of the tax. The tax will be broken down into 'sales tax', 'excise tax' and 'shipping tax' components. The break up will also be available on the line-item level to see the tax per item in the order, invoice and credit memo.
The break up will also be available in the order, invoice and credit memo listing pages in the Magento backend which can be downloaded as a report. This will enable the administrator to have the information for reporting and reconciliation of transactions.

## AvaTax Queue

The AvaTax Queue functionality only works when **Tax Mode** is set to **Estimate Tax & Submit Transactions to AvaTax.** The following section assumes that AvaTax queueing is enabled. To view the AvaTax Queue, in the Magento admin, go to `Stores > AvaTax Queue`. 

When invoices and credit memos are created in Magento, new records are added to the AvaTax Queue with a **pending** status. If a CRON job is properly configured, then every 5 minutes, all pending records will be submitted to AvaTax with a **Document Type** of **Sales Invoice** or **Return Invoice**, depending on whether the record is a Magento invoice or credit memo (respectively). If there are errors submitting the record, Magento will attempt to resend the record for the number of times configured in the **Max Queue Retry Attempts** constant property, this is set for maximum 3 attempts.

If you are in a development or staging environment and don't have a CRON job setup, or you want to send invoice and creitmemo records individually you can manually send these records to AvaTax using the **Post to AvaTax** button on the invoice and creditmemo details page in the Magento admin.

## AvaTax Logging

The logging functionality built into this extension is for debugging purposes. If you are experiencing issues with this extension, you can review the logs to see if they provide any details about the issues you are experiencing. 

This extension can log information in two locations: In files (in the var/log/ directory) and/or in the database (in `Stores > AvaTax Logs`), depending on the logging settings you have configured in `Stores > Settings > Configuration > Sales > Tax > AvaTax Settings > Logging Settings`.


## Magento Order and Invoice Numbers

If you are using AvaTax with a **Tax Mode** of **Estimate Tax & Submit Transactions to AvaTax**, when Invoices or Credit Memos get sent to AvaTax, the Invoice/Credit Memo 'id' will be sent in the **InvoiceNumber** field prefixed with ‘INV’ or ‘CM’ for invoice and credit memo, respectively. The Magento Order Number will be sent in the **CustomString** field.

