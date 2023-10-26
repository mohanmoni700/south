# Overview

This module provides status for PWA when the website is on Maintenance. 

 - [Documentation](#markdown-header-documentation)
 - [Installation](#markdown-header-installation)
 - [Specifications](#markdown-header-specifications)
 - [Compatible Magento Version](#markdown-header-compatibility-matrix)

## Documentation

* [WIKI](https://corratech.jira.com/wiki/spaces/EKC/pages/2375418141/Pwa+Maintenance+Check)

## Installation

**Install via Composer:** This is the recommended installation method as it allows you to easily update the extension in the future. Make sure this module is added to the project's packagist account.

    composer require corra/pwa-maintenance-check

## Specifications

 - Observer
	- maintenance_mode_changed > Corra\PwaMaintenanceCheck\Observer\Maintenance\ModeChanged

## Compatibility Matrix

**Module version:** Version of this Reusable Module

**Magento:** Version of Magento tested

| Module version | Magento    |
|----------------|------------|
| `1.0.0`        | `2.3.5-p2` |
| `1.0.1`        | `2.4.3-p1` |

