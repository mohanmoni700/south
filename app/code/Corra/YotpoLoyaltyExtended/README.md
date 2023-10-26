# Overview
This module provides GraphQL support for `Yotpo_Loyalty` module. The **Corra_YotpoLoyaltyExtended** module adds customer information required for yotpo loyality.

 - [Documentation](#markdown-header-documentation)
 - [Installation](#markdown-header-installation)
 - [Compatibility Matrix](#markdown-header-compatibility-matrix)
 - [Usage](#markdown-header-usage)
 
## Documentation

* [WIKI](https://corratech.jira.com/wiki/spaces/EKC/pages/3538944002/Yotpo+Loyalty+Extended)

## Installation
**Install via Composer:** This is the recommended installation method as it allows you to easily update the extension in the future. Make sure this module is added to the project's packagist account.

    composer require corra/module-yotpo-loyalty-extended

## Compatibility Matrix

**Module version:** Version of this Reusable Module

**Magento:** Version of Magento tested

**Yotpo Loyalty:** Version of module `yotpo/magento2-module-yotpo-loyalty` tested

| Module version  | Magento      | Yotpo Loyalty |
|-----------------|--------------|---------------|
| `1.0.1`         |  `2.4.3`     |  `1.2`        |



## Usage
### Get  customer information required for yotpo loyality.
```graphql
query {
    customer {
        yotpo_identification {
            identity
            tags
            token
        }
    }
}
```

**Response:**
```json
{
  "data": {
    "customer": {
      "yotpo_identification": {
        "identity": "1",
        "tags": "[General]",
        "token": "eceba15c57ef9ebdee54b89cc8a43ad4c61a61ed8279239001fe69e30457f3e979"
      }
    }
  }
}
```
