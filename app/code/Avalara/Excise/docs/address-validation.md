# Documentation

- [Home](../README.md)
- [Getting Started](./getting-started.md)
- Extension Features
  - [Excise Sales Tax](./excise-sales-tax.md)
  - [Address Validation](./address-validation.md)

# Address Validation

## Table Of Contents

- [Overview](#overview)
- [Configuration](#configuration)
- [Frontend Checkout](#frontend-checkout)
- [Frontend Add/Edit Customer Address](#frontend-add-edit-customer-address)
- [Backend Add/Edit Customer Address](#backend-add-edit-customer-address)
- [Backend Order Creation](#backend-order-creation)

## Overview

This extension implements address validation in nearly every area where an address can be entered:

- Frontend Checkout
- Frontend add/edit customer address
- Backend order creation
- Backend add/edit customer address

The following sections explain how address validation works in the four areas listed above. Note: Address validation is not enabled for virtual orders (orders where only a billing address is required).

## Configuration

1. In the Magento admin, go to `Stores > Settings > Configuration > Sales > Tax`. Click on the **AvaTax - Address Validation** section.
2. Review each of the options in this section and input the appropriate value. This is [a screenshot of the configuration options.](images/Configuration__Settings__Stores__Magento_Admin.png?raw=true)

## Frontend Checkout

When a guest or a signed in customer proceeds from the **Shipping** step to the **Review & Payment** step, the address they submitted will be sent to AvaTax to be validated. 

If the configuration setting **Allow User To Choose Original (Invalid) Address** has been set to **Yes**, both the Suggested Address and Original Address will be displayed:  

![](images/address_validation_with_choice.png?raw=true)

If the configuration setting **Allow User To Choose Original (Invalid) Address** has been set to **No**, only the valid address will be displayed to the user:  

![](images/address_validation_without_choice.png?raw=true)

If a signed in customer is checking out and their address gets validated, the address the customer selected will automatically be set to the suggested address once the customer proceeds to the **Review & Payment** step. If the customer selects the original address, their customer address will be updated to that address. This will happen every time the user selects a different address. 

If the user submits an address that AvaTax cannot validate, an [error message](https://help.avalara.com/kb/001/Common_Error_Messages_returned_with_GetTax_and_Validate_Requests#Common_Error_Messages) will be displayed to give some indication to the user that their address may be incorrect. This does not disrupt the checkout process:  

![](images/address_validation_unable_to_validate.png?raw=true)

If the address is already valid or if the address is from a country that is not on the list of **Enabled Countries** for address validation, nothing will be displayed to the user. 

If the user clicks the **edit your address** or **click here** links in the instructions, they will be navigated back to the **Shipping** step.

## Frontend Add/Edit Customer Address

When a customer is adding or editing an address tries to save that address, they will be presented with a modal displaying this **Verify Your Address** form:  

![](images/address_validation_customer_edit_address.png?raw=true)

If the customer clicks the **x** icon, the **edit your address** link, or the **Edit Address** button, the modal will close and the form will not be submitted. If the customer clicks **Save Address** with the suggested address selected, the fields which are highlighted will be updated in the form and the form will be submitted. Otherwise, the original address will be submitted without any modifications to the form fields. If the config field **Allow User To Choose Original (Invalid) Address** is set to **No**, and the customer clicks save address, the valid address will be submitted. If the API response returns an error, that error will be displayed to the customer just as it is in the checkout. The customer will then have the option of either editing their address or saving it. If the address is not located inside one of the enabled countries or is already valid, nothing will be displayed to the user and the form will be submitted normally.

## Backend Add/Edit Customer Address

Address validation in this area is triggered by clicking the **Validate Address** button at the bottom of an address form: 

![](images/address_validation_backend_edit_address.png?raw=true)

If the address is already valid, a success message will appear displaying the message *"This address is already valid"*. If the address is not from one of the enabled countries, an error message will appear displaying the message *"Address validation is not enabled for the country you selected"*. After the address has been validated, the suggested address will automatically be selected and the form will be updated. Selecting either address will update the form with that address. Clicking the **edit your address** link will scroll to the top of the page. 

If the address is unable to be validated (see screenshot below), a message will be displayed indicating the reason why the validation failed (e.g. *"An exact street name match could not be found"* or *"The address number is out of range"*). The customer address can be revised by clicking the provided link; saving the customer record at this point will save the address as entered.  

![](images/Veronica_Costello__Customers__Customers__Magento_Admin_2017-04-26_10-08-51.png?raw=true)

## Backend Order Creation

Address validation in this area is also triggered by clicking the **Validate Address** button of the bottom of the Billing or Shipping Address forms. If **Same As Billing Address** is checked, the **Validate Address** button will not exist below the Shipping Address form. After the **Validate Address** button is clicked, a modal will appear displaying the same form that is displayed in the frontend add/edit customer address area:  

![](images/Backend_Order_Address_Validation.png?raw=true)

![](images/address_validation_admin_order_creation.png?raw=true)

If the address is already valid, a success message will appear displaying the message *"This address is already valid"*. If the address is not from one of the enabled countries, an error message will appear displaying the message *"Address validation is not enabled for the country you selected"*. 
