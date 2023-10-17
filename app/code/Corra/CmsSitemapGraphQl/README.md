# Overview
This module **Corra_CmsSitemapGraphQl** is to retrieve the CMS page data for sitemap in pylot projects.

 - [Documentation](#markdown-header-documentation)
 - [Installation](#markdown-header-installation)
 - [Compatibility Matrix](#markdown-header-compatibility-matrix)
 - [Usage](#markdown-header-usage)

## Documentation

* [WIKI](https://corratech.jira.com/wiki/spaces/EKC/pages/3789848577/CmsSitemapGraphQl)

## Installation

**Install via Composer:** This is the recommended installation method as it allows you to easily update the extension in the future. Make sure this module is added to the project's packagist account.

   `composer require corra/module-cms-sitemap-graph-ql`

## Compatibility Matrix

**Module version:** Version of this Reusable Module

**Magento:** Version of Magento tested


| Module version  | Magento       | 
|-----------------|---------------|
| `1.0.2`         | `2.4.3-p2` 

## Usage
To include any cms page url in to sitemap, enable "Add In Sitemap" from Admin > Content > Edit any cms page > Search Engine Optimization
**Get Cms Sitemap Data**
```graphql
query {
        CmsSitemapData {
          url_key
          title
          updated_at
        }
      }
```

### Admin Configuration

* No admin configurations

