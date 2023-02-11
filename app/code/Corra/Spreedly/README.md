# Overview

This module creates the GraphQL support for Zendesk classyllama module.

 - [Documentation](#markdown-header-documentation)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Compatibility Matrix](#markdown-header-compatibility-matrix)
 - [Usage](#markdown-header-usage)

## Documentation

* [WIKI](https://corratech.jira.com/wiki/spaces/EKC/pages/3579707442/Spreedly+PaymentGateway)

## Installation

Install via Composer:
This is the recommended installation method as it allows you to easily update the extension in the future. Make sure this module is added to the project's packagist account.

    composer require corra/module-spreedly

## Configuration
The configuration can be found in the Magento 2 admin panel under  
Store->Configuration->Sales->Payment Methods->Spreedly  

## Compatibility Matrix

**Module version:** Version of this Reusable Module

**Magento:** Version of Magento tested


| Module version  | Magento       | 
|-----------------|---------------|
| `1.0.0`         | `2.4.3-p1`       | 


## Usage

Admin User can place Orders in Magento Admin panel Sales > Orders > New Orders
Orders can place using Credit Card and it will go to Spreedly Gateway for all Transaction.





