# Overview
The **Corra_QuoteBundleItemStock** validates the qty of bundle quote items and display out of stock message if any selected child product is not available(Out of Stock).


 - [Documentation](#markdown-header-documentation)
 - [Installation](#markdown-header-installation)
 - [Compatibility Matrix](#markdown-header-compatibility-matrix)
 - [Usage](#markdown-header-usage)

## Documentation

* [WIKI](https://corratech.jira.com/wiki/spaces/EKC/pages/3917054144/Bundle+Item+Stock+Status+In+cart)


## Installation

**Install via Composer:** This is the recommended installation method as it allows you to easily update the extension in the future. Make sure this module is added to the project's packagist account.

   `composer require corra/module-quote-bundle-item-stock`


## Compatibility Matrix

**Module version:** Version of this Reusable Module

**Magento:** Version of Magento tested


| Module version | Magento       | 
|----------------|---------------|
| `1.0.2`        | `2.4.3-p1`  

## Usage

- Checks the quote bundle item's qty and returns the out of stock message in graphql response if any selected child product is out of stock. Please find the graphql request format below:
  ```graphql
  {
    customerCart {
      items{
          is_out_stock
          oos_message
       }
    }
  }
  ```

### Admin Configuration

* No admin configurations
