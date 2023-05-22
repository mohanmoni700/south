1.1.9
=====
* Added support for Gateway Specific Fields (enables support to checkout.com)

1.1.8
=====
* Log message as info rather than error when redacted card cron configuration is disabled.

1.1.7
=====
* Display the actual error message returned from gateway.

1.1.6
=====
* Removing the Redacted Saved CC cards.

1.1.5
=====
* Fixed issue to retain the saved cc by passing retained as true.

1.1.4
=====
* Fixed issue with complete amount captured for partial invoice.

1.1.3
=====
* Fixed issue with order_id not displayed in Spreedly Dasbhoard

1.1.2
=====
* Added API Url in the logger
* Refund Issue Fixed

1.1.1
=====
* Added Debug/Logger support for All Transaction

1.1.0
=====
* Fixed issue when public_hash is being lost if shipping method is selected after saved card payment method is selected

1.0.9
=====
* Fixed missing Transaction details when saved card is used

1.0.8
=====
* Added Refund support for Transaction

1.0.7
=====
* GraphQL support to pay with saved CC

1.0.6
=====
* Added Vault support for M2 Admin panel

1.0.5
=====
* Added Void and Cancel of Transaction

1.0.4
=====
* Coding Standard Fix
* Added GatewayToken Switch, based on the Distribution Value

1.0.3
=====
* Added Capture of Payments

1.0.2
=====
* Changed Graphql schema name option value of additional_data (cc_token to payment_method_token)

1.0.1
=====
* Added Graphql option for sending the setPaymentMethodCart with additional_data for the orders

1.0.0
=====
* Initial Release
* Added Authroize of Transaction



