# OverView

- This module adds the graphql support to Signifyd Connect module. 
- Added the graphQl to check the Signifyd is active or not.
- It generates a unique session ID for PWA to use in the frontend to send to Signifyd as a tracking called Signifyd Fingerprints.

## Documentation
[WIKI](https://corratech.jira.com/wiki/spaces/EKC/pages/1938030702/SignifydGraphQl)

## Installation

- Install via Composer: This is the recommended installation method as it allows you to easily update the extension in the future. Make sure this module is added to the project's packagist account.

     composer require corra/module-signifyd-graph-ql

## Compatibility Matrix

**Module version:** Version of this Reusable Module

**Magento:** Version of Magento tested

| Module version  | Magento            | 
|-----------------|--------------------|
| `1.0.2`         | `2.3.5-p2, 2.3.6`  |
| `1.0.3`         | `2.4.3`            |

## Usage

```graphql
{
    storeConfig {
        enable_signifyd
    }
}
```

- This is a Boolean value
- If its enabled PWA should call below query in all pages to get the unique session ID to build the tracking script for Signifyd.
- If possible this query result should be saved in browser and should update only when cart is changed.

Example:

```graphql
mutation {
    generateSignifydSessionId(cart_id:"j7aqhAJ7dx3eq3t6AA5oAwgWzXq9TKC") {
        data_order_session_id
    }
}
```

`cart_id` should be the cart mask id 
`data_order_session_id` will be a string

## Magento Admin Configuration

- Enable and configure the Signifyd Extension from Admin → Stores → Configuration → Services → Signifyd