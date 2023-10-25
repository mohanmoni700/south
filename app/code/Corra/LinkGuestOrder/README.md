# Overview
The **Corra_LinkGuestOrder** is a reusable module consists of mutations based to create a customer on checkout and lining the order increment id.

- [Installation](#markdown-header-installation)
- [Compatibility Matrix](#markdown-header-compatibility-matrix)
- [Usage](#markdown-header-usage)

## Installation

**Install via Composer:** This is the recommended installation method as it allows you to easily update the extension in the future. Make sure this module is added to the project's packagist account.

`corra/module-link-guest-order-graph-ql`

## Compatibility Matrix

**Module version:** Version of this Reusable Module

**Magento:** Version of Magento tested

| Module version | Magento    | 
|----------------|------------|
| `1.0.0`        | 2.4.3-p2 ` |

## Usage

Graphql Mutation

```
mutation {
  createCustomerOnCheckout(
    input:{
		email:"tester@corra.com"
        firstname:"testing"
        lastname: "tester"
        date_of_birth:"04/03/1998"
    	password:"Test#123"
        is_subscribed: true
    }
    orderId:"150214927"
  ){
    customer{
      firstname
      email
    }
  }
}
```
### Admin Configuration

* No admin configurations required.