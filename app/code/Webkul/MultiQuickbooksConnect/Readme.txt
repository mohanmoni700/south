#Installation

Magento2 Multi Quickbooks Connect module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and MultiQuickbooksConnect(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/MultiQuickbooksConnect/ folder.

Run Following Command via terminal
-----------------------------------
composer require quickbooks/v3-php-sdk

php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly installed

#User Guide

For Magento2 Multi Quickbooks Connect module's working process follow user guide - https://webkul.com/blog/magento2-quickbooks-multi-user-connector/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
