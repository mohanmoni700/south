<?php
return [
    'scopes' => [
        'websites' => [
            'admin' => [
                'website_id' => '0',
                'code' => 'admin',
                'name' => 'Admin',
                'sort_order' => '0',
                'default_group_id' => '0',
                'is_default' => '0'
            ],
            'base' => [
                'website_id' => '1',
                'code' => 'base',
                'name' => 'Main Website',
                'sort_order' => '0',
                'default_group_id' => '1',
                'is_default' => '1'
            ],
            'hookah' => [
                'website_id' => '2',
                'code' => 'hookah',
                'name' => 'Shisha-World.com (B2C)',
                'sort_order' => '30',
                'default_group_id' => '2',
                'is_default' => '0'
            ],
            'hookah_company' => [
                'website_id' => '5',
                'code' => 'hookah_company',
                'name' => 'Hookah-company.com (B2C)',
                'sort_order' => '20',
                'default_group_id' => '5',
                'is_default' => '0'
            ],
            'hookah_wholesalers' => [
                'website_id' => '8',
                'code' => 'hookah_wholesalers',
                'name' => 'Hookah Wholesalers (B2B)',
                'sort_order' => '10',
                'default_group_id' => '8',
                'is_default' => '0'
            ],
            'ooka_de' => [
                'website_id' => '11',
                'code' => 'ooka_de',
                'name' => 'Ooka DE',
                'sort_order' => '0',
                'default_group_id' => '14',
                'is_default' => '0'
            ],
            'ooka_usa' => [
                'website_id' => '14',
                'code' => 'ooka_usa',
                'name' => 'Ooka USA',
                'sort_order' => '0',
                'default_group_id' => '11',
                'is_default' => '0'
            ],
            'ooka' => [
                'website_id' => '17',
                'code' => 'ooka',
                'name' => 'Ooka.com',
                'sort_order' => '0',
                'default_group_id' => '17',
                'is_default' => '0'
            ],
            'shisha_world_b2b' => [
                'website_id' => '18',
                'code' => 'shisha_world_b2b',
                'name' => 'Shisha-World.com (B2B)',
                'sort_order' => '40',
                'default_group_id' => '18',
                'is_default' => '0'
            ],
            'ooka_uae' => [
                'website_id' => '21',
                'code' => 'ooka_uae',
                'name' => 'Ooka UAE',
                'sort_order' => '0',
                'default_group_id' => '21',
                'is_default' => '0'
            ],
            'global_hookah' => [
                'website_id' => '23',
                'code' => 'global_hookah',
                'name' => 'Global Hookah (B2B)',
                'sort_order' => '50',
                'default_group_id' => '23',
                'is_default' => '0'
            ]
        ],
        'groups' => [
            0 => [
                'group_id' => '0',
                'website_id' => '0',
                'name' => 'Default',
                'root_category_id' => '0',
                'default_store_id' => '0',
                'code' => 'default'
            ],
            1 => [
                'group_id' => '1',
                'website_id' => '1',
                'name' => 'Hookah-Shisha.com (B2C) Store',
                'root_category_id' => '2',
                'default_store_id' => '1',
                'code' => 'main_website_store'
            ],
            2 => [
                'group_id' => '2',
                'website_id' => '2',
                'name' => 'Shisha-World.com (B2C) Store',
                'root_category_id' => '650',
                'default_store_id' => '2',
                'code' => 'hookah_store'
            ],
            5 => [
                'group_id' => '5',
                'website_id' => '5',
                'name' => 'Hookah-company.com (B2C) Store',
                'root_category_id' => '2',
                'default_store_id' => '5',
                'code' => 'hookah_company_store'
            ],
            8 => [
                'group_id' => '8',
                'website_id' => '8',
                'name' => 'Hookah Wholesalers (B2B) Store',
                'root_category_id' => '705',
                'default_store_id' => '8',
                'code' => 'hookah_wholesalers_store'
            ],
            11 => [
                'group_id' => '11',
                'website_id' => '14',
                'name' => 'Ooka USA Store',
                'root_category_id' => '1133',
                'default_store_id' => '17',
                'code' => 'ooka_usa_store'
            ],
            14 => [
                'group_id' => '14',
                'website_id' => '11',
                'name' => 'Ooka DE Store',
                'root_category_id' => '1136',
                'default_store_id' => '11',
                'code' => 'ooka_de_store'
            ],
            17 => [
                'group_id' => '17',
                'website_id' => '17',
                'name' => 'Ooka Store',
                'root_category_id' => '1130',
                'default_store_id' => '20',
                'code' => 'ooka_store'
            ],
            18 => [
                'group_id' => '18',
                'website_id' => '18',
                'name' => 'Shisha-World.com (B2B) Store',
                'root_category_id' => '1182',
                'default_store_id' => '27',
                'code' => 'shisha_world_b2b_store'
            ],
            21 => [
                'group_id' => '21',
                'website_id' => '21',
                'name' => 'Ooka UAE Store',
                'root_category_id' => '2640',
                'default_store_id' => '36',
                'code' => 'ooka_uae_store'
            ],
            23 => [
                'group_id' => '23',
                'website_id' => '23',
                'name' => 'Global Hookah (B2B) Store',
                'root_category_id' => '3362',
                'default_store_id' => '41',
                'code' => 'global_hookah_store'
            ]
        ],
        'stores' => [
            'admin' => [
                'store_id' => '0',
                'code' => 'admin',
                'website_id' => '0',
                'group_id' => '0',
                'name' => 'Admin',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'default' => [
                'store_id' => '1',
                'code' => 'default',
                'website_id' => '1',
                'group_id' => '1',
                'name' => 'Hookah-Shisha',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'hookah_store_view_en' => [
                'store_id' => '2',
                'code' => 'hookah_store_view_en',
                'website_id' => '2',
                'group_id' => '2',
                'name' => 'Shisha-World.com (B2C) Store View EN',
                'sort_order' => '1',
                'is_active' => '1'
            ],
            'hookah_company_store_view' => [
                'store_id' => '5',
                'code' => 'hookah_company_store_view',
                'website_id' => '5',
                'group_id' => '5',
                'name' => 'Hookah-company.com (B2C) Store View',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'hookah_wholesalers_store_view' => [
                'store_id' => '8',
                'code' => 'hookah_wholesalers_store_view',
                'website_id' => '8',
                'group_id' => '8',
                'name' => 'Hookah Wholesalers (B2B) Store View',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'ooka_de_store_de' => [
                'store_id' => '11',
                'code' => 'ooka_de_store_de',
                'website_id' => '11',
                'group_id' => '14',
                'name' => 'Ooka DE (DE) Store View',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'ooka_de_store_en' => [
                'store_id' => '14',
                'code' => 'ooka_de_store_en',
                'website_id' => '11',
                'group_id' => '14',
                'name' => 'Ooka DE (EN) Store View',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'ooka_usa_store_en' => [
                'store_id' => '17',
                'code' => 'ooka_usa_store_en',
                'website_id' => '14',
                'group_id' => '11',
                'name' => 'Ooka USA (EN) Store View',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'ooka_store_view' => [
                'store_id' => '20',
                'code' => 'ooka_store_view',
                'website_id' => '17',
                'group_id' => '17',
                'name' => 'Ooka.com Store View',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'hookah_store_view_de' => [
                'store_id' => '21',
                'code' => 'hookah_store_view_de',
                'website_id' => '2',
                'group_id' => '2',
                'name' => 'Shisha-World.com (B2C) Store View DE',
                'sort_order' => '1',
                'is_active' => '1'
            ],
            'hookah_store_view_es' => [
                'store_id' => '24',
                'code' => 'hookah_store_view_es',
                'website_id' => '2',
                'group_id' => '2',
                'name' => 'Shisha-World.com (B2C) Store View ES',
                'sort_order' => '3',
                'is_active' => '1'
            ],
            'shisha_world_b2b_store_view_de' => [
                'store_id' => '27',
                'code' => 'shisha_world_b2b_store_view_de',
                'website_id' => '18',
                'group_id' => '18',
                'name' => 'DE',
                'sort_order' => '1',
                'is_active' => '1'
            ],
            'shisha_world_b2b_store_view_en' => [
                'store_id' => '30',
                'code' => 'shisha_world_b2b_store_view_en',
                'website_id' => '18',
                'group_id' => '18',
                'name' => 'EN',
                'sort_order' => '2',
                'is_active' => '1'
            ],
            'shisha_world_b2b_store_view_es' => [
                'store_id' => '33',
                'code' => 'shisha_world_b2b_store_view_es',
                'website_id' => '18',
                'group_id' => '18',
                'name' => 'ES',
                'sort_order' => '3',
                'is_active' => '1'
            ],
            'ooka_uae_store_en' => [
                'store_id' => '36',
                'code' => 'ooka_uae_store_en',
                'website_id' => '21',
                'group_id' => '21',
                'name' => 'Ooka UAE (EN) Store View',
                'sort_order' => '0',
                'is_active' => '1'
            ],
            'ooka_uae_store_ar' => [
                'store_id' => '39',
                'code' => 'ooka_uae_store_ar',
                'website_id' => '21',
                'group_id' => '21',
                'name' => 'Ooka UAE (AR) Store View',
                'sort_order' => '1',
                'is_active' => '1'
            ],
            'global_hookah_store_view' => [
                'store_id' => '41',
                'code' => 'global_hookah_store_view',
                'website_id' => '23',
                'group_id' => '23',
                'name' => 'Global Hookah (B2B) Store View',
                'sort_order' => '1',
                'is_active' => '1'
            ]
        ]
    ],
    'system' => [
        'default' => [
            'advanced' => [
                'modules_disable_output' => [
                    'Magento_Banner' => '1'
                ]
            ],
            'general' => [
                'locale' => [
                    'code' => 'en_US'
                ]
            ],
            'dev' => [
                'static' => [
                    'sign' => '1'
                ],
                'front_end_development_workflow' => [
                    'type' => 'server_side_compilation'
                ],
                'template' => [
                    'allow_symlink' => null,
                    'minify_html' => '0'
                ],
                'js' => [
                    'merge_files' => '1',
                    'enable_js_bundling' => null,
                    'minify_files' => '0',
                    'move_script_to_bottom' => '0',
                    'translate_strategy' => 'dictionary',
                    'session_storage_logging' => '0',
                    'minify_exclude' => [
                        'tiny_mce' => '/tiny_mce/',
                        'cardinal_commerce' => '/v1/songbird'
                    ]
                ],
                'css' => [
                    'merge_css_files' => '1',
                    'minify_files' => '0',
                    'use_css_critical_path' => '0',
                    'minify_exclude' => [
                        'tiny_mce' => '/tiny_mce/'
                    ]
                ]
            ]
        ],
        'stores' => [
            'admin' => [
                'design' => [
                    'package' => [
                        'name' => 'default'
                    ],
                    'theme' => [
                        'default' => 'default'
                    ]
                ]
            ],
            'default' => [
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Corra/alfakher'
                    ]
                ]
            ],
            'ooka_de_store_de' => [
                'general' => [
                    'locale' => [
                        'code' => 'de_DE'
                    ]
                ]
            ],
            'shisha_world_b2b_store_view_de' => [
                'general' => [
                    'locale' => [
                        'code' => 'de_DE'
                    ]
                ]
            ],
            'shisha_world_b2b_store_view_es' => [
                'general' => [
                    'locale' => [
                        'code' => 'es_ES'
                    ]
                ]
            ],
            'hookah_store_view_es' => [
                'general' => [
                    'locale' => [
                        'code' => 'es_ES'
                    ]
                ]
            ],
            'hookah_store_view_de' => [
                'general' => [
                    'locale' => [
                        'code' => 'de_DE'
                    ]
                ]
            ],
            'ooka_uae_store_ar' => [
                'general' => [
                    'locale' => [
                        'code' => 'ar_SA'
                    ]
                ]
            ]
        ],
        'websites' => [
            'admin' => [
                'web' => [
                    'routers' => [
                        'frontend' => [
                            'disabled' => 'true'
                        ]
                    ],
                    'default' => [
                        'no_route' => 'admin/noroute/index'
                    ]
                ]
            ],
            'base' => [
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Corra/alfakher'
                    ]
                ]
            ],
            'hookah' => [
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Corra/alfakher'
                    ]
                ]
            ],
            'hookah_company' => [
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Corra/alfakher'
                    ]
                ]
            ],
            'hookah_wholesalers' => [
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Alfakher/hookahwholesalers'
                    ]
                ]
            ],
            'ooka' => [
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Corra/ooka'
                    ]
                ]
            ],
            'ooka_usa' => [
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Corra/ooka'
                    ]
                ]
            ],
            'ooka_de' => [
                'currency' => [
                    'options' => [
                        'base' => 'EUR',
                        'default' => 'EUR',
                        'allow' => 'EUR'
                    ]
                ],
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Corra/ooka-de'
                    ]
                ]
            ],
            'shisha_world_b2b' => [
                'currency' => [
                    'options' => [
                        'base' => 'EUR',
                        'default' => 'EUR',
                        'allow' => 'EUR'
                    ]
                ],
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Alfakher/shishaworld'
                    ]
                ]
            ],
            'global_hookah' => [
                'design' => [
                    'theme' => [
                        'theme_id' => 'frontend/Alfakher/globalhookah'
                    ]
                ]
            ]
        ]
    ],
    'modules' => [
        'Magento_AdminAnalytics' => 1,
        'Magento_Store' => 1,
        'Magento_AdminGwsConfigurableProduct' => 1,
        'Magento_AdminGwsStaging' => 1,
        'Magento_Directory' => 1,
        'Magento_AdobeIms' => 1,
        'Magento_AdobeImsApi' => 1,
        'Magento_AdobeStockAdminUi' => 1,
        'Magento_MediaGallery' => 1,
        'Magento_AdobeStockAssetApi' => 1,
        'Magento_AdobeStockClient' => 1,
        'Magento_AdobeStockClientApi' => 1,
        'Magento_AdobeStockImage' => 1,
        'Magento_Theme' => 1,
        'Magento_AdobeStockImageApi' => 1,
        'Magento_Eav' => 1,
        'Magento_Customer' => 1,
        'Magento_AdvancedPricingImportExport' => 1,
        'Magento_Rule' => 1,
        'Magento_AdminNotification' => 1,
        'Magento_Backend' => 1,
        'Magento_Amqp' => 1,
        'Magento_AmqpStore' => 1,
        'Magento_Config' => 1,
        'Magento_User' => 1,
        'Magento_Authorization' => 1,
        'Magento_Indexer' => 1,
        'Magento_AwsS3CustomerCustomAttributes' => 1,
        'Magento_AwsS3GiftCardImportExport' => 1,
        'Magento_Variable' => 1,
        'Magento_AwsS3ScheduledImportExport' => 1,
        'Magento_Cms' => 1,
        'Magento_Catalog' => 1,
        'Magento_Backup' => 1,
        'Magento_CatalogRule' => 1,
        'Magento_Quote' => 1,
        'Magento_SalesSequence' => 1,
        'Magento_Payment' => 1,
        'Magento_Sales' => 1,
        'Magento_Bundle' => 1,
        'Magento_GraphQl' => 1,
        'Magento_BundleImportExport' => 1,
        'Magento_BundleImportExportStaging' => 1,
        'Magento_CatalogInventory' => 1,
        'Magento_RequisitionList' => 1,
        'Magento_EavGraphQl' => 1,
        'Magento_Search' => 1,
        'Magento_Checkout' => 1,
        'Magento_CacheInvalidate' => 1,
        'Magento_MediaStorage' => 1,
        'Magento_CardinalCommerce' => 1,
        'Magento_AdvancedCatalog' => 1,
        'Magento_Security' => 1,
        'Magento_CmsGraphQl' => 1,
        'Magento_StoreGraphQl' => 1,
        'Magento_CatalogSearch' => 1,
        'Magento_CatalogGraphQl' => 1,
        'Magento_CatalogImportExport' => 1,
        'Magento_CatalogImportExportStaging' => 1,
        'Magento_Widget' => 1,
        'Magento_CatalogInventoryGraphQl' => 1,
        'Magento_CatalogUrlRewrite' => 1,
        'Magento_CatalogPageBuilderAnalytics' => 1,
        'Magento_CatalogPageBuilderAnalyticsStaging' => 1,
        'Magento_CustomerCustomAttributes' => 1,
        'Magento_Ui' => 1,
        'Magento_CustomerSegment' => 1,
        'Magento_Msrp' => 1,
        'Magento_CatalogRuleGraphQl' => 1,
        'Magento_SalesRule' => 1,
        'Magento_Downloadable' => 1,
        'Magento_GiftCard' => 1,
        'Magento_Staging' => 1,
        'Magento_Captcha' => 1,
        'Magento_CatalogEvent' => 1,
        'Magento_UrlRewriteGraphQl' => 1,
        'Magento_ConfigurableProduct' => 1,
        'Magento_Robots' => 1,
        'Magento_AdvancedCheckout' => 1,
        'Magento_CheckoutAddressSearch' => 1,
        'Magento_Wishlist' => 1,
        'Magento_ScalableOms' => 1,
        'Magento_CheckoutAgreements' => 1,
        'Magento_CheckoutAgreementsGraphQl' => 1,
        'Magento_CheckoutAgreementsNegotiableQuote' => 1,
        'Magento_Company' => 1,
        'Magento_CheckoutStaging' => 1,
        'Magento_CloudComponents' => 1,
        'Magento_MediaGalleryUi' => 1,
        'Magento_CatalogCmsGraphQl' => 1,
        'Magento_CmsPageBuilderAnalytics' => 1,
        'Magento_CmsPageBuilderAnalyticsStaging' => 1,
        'Magento_VersionsCms' => 1,
        'Magento_CmsUrlRewrite' => 1,
        'Magento_CmsUrlRewriteGraphQl' => 1,
        'Magento_Tax' => 1,
        'Magento_Integration' => 1,
        'Magento_CustomerGraphQl' => 1,
        'Magento_CompanyGraphQl' => 0,
        'Magento_CompanyPayment' => 1,
        'Magento_Shipping' => 1,
        'Magento_CompareListGraphQl' => 1,
        'Magento_ComposerRootUpdatePlugin' => 1,
        'Magento_Analytics' => 1,
        'Magento_ConfigurableImportExport' => 1,
        'Magento_NegotiableQuote' => 1,
        'Magento_CatalogRuleConfigurable' => 1,
        'Magento_QuoteGraphQl' => 1,
        'Magento_ConfigurableProductSales' => 1,
        'Magento_PageCache' => 1,
        'Magento_ConfigurableRequisitionList' => 1,
        'Magento_RequisitionListGraphQl' => 0,
        'Magento_WebsiteRestriction' => 1,
        'Magento_Contact' => 1,
        'Magento_Cookie' => 1,
        'Magento_Cron' => 1,
        'Magento_Csp' => 1,
        'Magento_CurrencySymbol' => 1,
        'Magento_CustomAttributeManagement' => 1,
        'Magento_BundleNegotiableQuote' => 1,
        'Magento_CustomerAnalytics' => 1,
        'Magento_CustomerBalance' => 1,
        'Magento_CustomerBalanceGraphQl' => 1,
        'Magento_Banner' => 1,
        'Magento_DownloadableGraphQl' => 1,
        'Magento_CustomerFinance' => 1,
        'Magento_CompanyCreditGraphQl' => 0,
        'Magento_CustomerImportExport' => 1,
        'Magento_CatalogWidget' => 1,
        'Magento_Deploy' => 1,
        'Magento_Developer' => 1,
        'Magento_Dhl' => 1,
        'Magento_AdvancedSearch' => 1,
        'Magento_DirectoryGraphQl' => 1,
        'Magento_ProductAlert' => 1,
        'Magento_CustomerDownloadableGraphQl' => 1,
        'Magento_ImportExport' => 1,
        'Magento_DownloadableRequisitionListGraphQl' => 0,
        'Magento_TargetRule' => 1,
        'Magento_AdvancedRule' => 1,
        'Magento_BundleGraphQl' => 1,
        'Magento_Elasticsearch' => 1,
        'Magento_Elasticsearch6' => 1,
        'Magento_Elasticsearch7' => 1,
        'Magento_CatalogPermissions' => 1,
        'Magento_Email' => 1,
        'Magento_EncryptionKey' => 1,
        'Magento_Enterprise' => 1,
        'Magento_Fedex' => 1,
        'Magento_VisualMerchandiser' => 1,
        'Magento_GiftCardAccount' => 1,
        'Magento_GiftCardAccountGraphQl' => 1,
        'Magento_WishlistGraphQl' => 1,
        'Magento_GiftCardImportExport' => 1,
        'Magento_GiftCardNegotiableQuote' => 1,
        'Magento_GiftCardRequisitionList' => 1,
        'Magento_GiftCardGraphQl' => 1,
        'Magento_SharedCatalog' => 1,
        'Magento_Weee' => 1,
        'Magento_GiftMessage' => 1,
        'Magento_GiftMessageGraphQl' => 1,
        'Magento_GiftMessageStaging' => 1,
        'Magento_GiftRegistry' => 1,
        'Magento_GiftRegistryGraphQl' => 1,
        'Magento_GiftWrapping' => 1,
        'Magento_GiftWrappingGraphQl' => 1,
        'Magento_GiftWrappingStaging' => 1,
        'Magento_GoogleAdwords' => 1,
        'Magento_GoogleAnalytics' => 1,
        'Magento_GoogleOptimizer' => 1,
        'Magento_GoogleOptimizerStaging' => 1,
        'Magento_GoogleShoppingAds' => 1,
        'Magento_GoogleTagManager' => 1,
        'Magento_CatalogCustomerGraphQl' => 1,
        'Magento_GraphQlCache' => 1,
        'Magento_GroupedProduct' => 1,
        'Magento_GroupedImportExport' => 1,
        'Magento_GroupedCatalogInventory' => 1,
        'Magento_GroupedProductGraphQl' => 1,
        'Magento_GroupedProductStaging' => 1,
        'Magento_GroupedRequisitionList' => 1,
        'Magento_GroupedSharedCatalog' => 1,
        'Magento_DownloadableImportExport' => 1,
        'Magento_Sitemap' => 1,
        'Magento_InstantPurchase' => 1,
        'Magento_CatalogAnalytics' => 1,
        'Magento_Inventory' => 0,
        'Magento_InventoryAdminUi' => 0,
        'Magento_InventoryAdvancedCheckout' => 0,
        'Magento_InventoryApi' => 0,
        'Magento_InventoryBundleImportExport' => 0,
        'Magento_InventoryBundleProduct' => 0,
        'Magento_InventoryBundleProductAdminUi' => 0,
        'Magento_InventoryBundleProductIndexer' => 0,
        'Magento_InventoryCatalog' => 0,
        'Magento_InventorySales' => 0,
        'Magento_InventoryCatalogAdminUi' => 0,
        'Magento_InventoryCatalogApi' => 0,
        'Magento_InventoryCatalogFrontendUi' => 0,
        'Magento_InventoryCatalogSearch' => 0,
        'Magento_InventoryCatalogSearchBundleProduct' => 0,
        'Magento_InventoryCatalogSearchConfigurableProduct' => 0,
        'Magento_ConfigurableProductGraphQl' => 1,
        'Magento_InventoryConfigurableProduct' => 0,
        'Magento_InventoryConfigurableProductFrontendUi' => 0,
        'Magento_InventoryConfigurableProductIndexer' => 0,
        'Magento_InventoryConfiguration' => 0,
        'Magento_InventoryConfigurationApi' => 0,
        'Magento_InventoryDistanceBasedSourceSelection' => 0,
        'Magento_InventoryDistanceBasedSourceSelectionAdminUi' => 0,
        'Magento_InventoryDistanceBasedSourceSelectionApi' => 0,
        'Magento_InventoryElasticsearch' => 0,
        'Magento_InventoryExportStockApi' => 0,
        'Magento_InventoryIndexer' => 0,
        'Magento_InventorySalesApi' => 0,
        'Magento_InventoryGroupedProduct' => 0,
        'Magento_InventoryGroupedProductAdminUi' => 0,
        'Magento_InventoryGroupedProductIndexer' => 0,
        'Magento_InventoryImportExport' => 0,
        'Magento_InventoryInStorePickupApi' => 0,
        'Magento_InventoryInStorePickupAdminUi' => 0,
        'Magento_InventorySourceSelectionApi' => 0,
        'Magento_InventoryInStorePickup' => 0,
        'Magento_InventoryInStorePickupGraphQl' => 0,
        'Magento_InventoryInStorePickupShippingApi' => 0,
        'Magento_InventoryInStorePickupQuote' => 0,
        'Magento_InventoryInStorePickupQuoteGraphQl' => 0,
        'Magento_InventoryInStorePickupSales' => 0,
        'Magento_InventoryInStorePickupSalesApi' => 0,
        'Magento_InventoryInStorePickupSalesAdminUi' => 0,
        'Magento_InventoryInStorePickupShipping' => 0,
        'Magento_InventoryInStorePickupShippingAdminUi' => 0,
        'Magento_Multishipping' => 1,
        'Magento_Webapi' => 1,
        'Magento_InventoryCache' => 0,
        'Magento_InventoryLowQuantityNotification' => 0,
        'Magento_Reports' => 1,
        'Magento_InventoryLowQuantityNotificationApi' => 0,
        'Magento_InventoryMultiDimensionalIndexerApi' => 0,
        'Magento_InventoryProductAlert' => 0,
        'Magento_InventoryQuoteGraphQl' => 0,
        'Magento_InventoryRequisitionList' => 0,
        'Magento_InventoryReservations' => 0,
        'Magento_InventoryReservationCli' => 0,
        'Magento_InventoryReservationsApi' => 0,
        'Magento_InventoryExportStock' => 0,
        'Magento_InventorySalesAdminUi' => 0,
        'Magento_InventoryGraphQl' => 0,
        'Magento_InventorySalesFrontendUi' => 0,
        'Magento_InventorySetupFixtureGenerator' => 0,
        'Magento_InventoryShipping' => 0,
        'Magento_InventoryShippingAdminUi' => 0,
        'Magento_InventorySourceDeductionApi' => 0,
        'Magento_InventorySourceSelection' => 0,
        'Magento_InventoryInStorePickupFrontend' => 0,
        'Magento_InventorySwatchesFrontendUi' => 0,
        'Magento_InventoryVisualMerchandiser' => 0,
        'Magento_InventoryWishlist' => 0,
        'Magento_Invitation' => 1,
        'Magento_JwtFrameworkAdapter' => 1,
        'Magento_LayeredNavigation' => 1,
        'Magento_LayeredNavigationStaging' => 1,
        'Magento_Logging' => 1,
        'Magento_LoginAsCustomer' => 1,
        'Magento_LoginAsCustomerAdminUi' => 1,
        'Magento_LoginAsCustomerApi' => 1,
        'Magento_LoginAsCustomerAssistance' => 1,
        'Magento_LoginAsCustomerFrontendUi' => 1,
        'Magento_LoginAsCustomerGraphQl' => 1,
        'Magento_LoginAsCustomerLog' => 1,
        'Magento_LoginAsCustomerLogging' => 1,
        'Magento_LoginAsCustomerPageCache' => 1,
        'Magento_LoginAsCustomerQuote' => 1,
        'Magento_LoginAsCustomerSales' => 1,
        'Magento_LoginAsCustomerWebsiteRestriction' => 1,
        'Magento_Marketplace' => 1,
        'Magento_MediaContent' => 1,
        'Magento_MediaContentApi' => 1,
        'Magento_MediaContentCatalog' => 1,
        'Magento_MediaContentCatalogStaging' => 1,
        'Magento_MediaContentCms' => 1,
        'Magento_MediaContentSynchronization' => 1,
        'Magento_MediaContentSynchronizationApi' => 1,
        'Magento_MediaContentSynchronizationCatalog' => 1,
        'Magento_MediaContentSynchronizationCms' => 1,
        'Magento_AdobeStockAsset' => 1,
        'Magento_MediaGalleryApi' => 1,
        'Magento_MediaGalleryCatalog' => 1,
        'Magento_MediaGalleryCatalogIntegration' => 1,
        'Magento_MediaGalleryCatalogUi' => 1,
        'Magento_MediaGalleryCmsUi' => 1,
        'Magento_MediaGalleryIntegration' => 1,
        'Magento_MediaGalleryMetadata' => 1,
        'Magento_MediaGalleryMetadataApi' => 1,
        'Magento_MediaGalleryRenditions' => 1,
        'Magento_MediaGalleryRenditionsApi' => 1,
        'Magento_MediaGallerySynchronization' => 1,
        'Magento_MediaGallerySynchronizationApi' => 1,
        'Magento_MediaGallerySynchronizationMetadata' => 1,
        'Magento_AdobeStockImageAdminUi' => 1,
        'Magento_MediaGalleryUiApi' => 1,
        'Magento_UrlRewrite' => 1,
        'Magento_MessageQueue' => 1,
        'Magento_CatalogStaging' => 1,
        'Magento_MsrpConfigurableProduct' => 1,
        'Magento_MsrpGroupedProduct' => 1,
        'Magento_MsrpStaging' => 1,
        'Magento_MultipleWishlist' => 1,
        'Magento_SalesGraphQl' => 1,
        'Magento_InventoryInStorePickupMultishipping' => 0,
        'Magento_MysqlMq' => 1,
        'Magento_CheckoutAddressSearchNegotiableQuote' => 1,
        'Magento_NegotiableQuoteGraphQl' => 0,
        'Magento_NegotiableQuoteSharedCatalog' => 1,
        'Magento_NegotiableQuoteWeee' => 1,
        'Magento_NewRelicReporting' => 1,
        'Magento_Newsletter' => 1,
        'Magento_NewsletterGraphQl' => 1,
        'Magento_OfflinePayments' => 1,
        'Magento_OfflineShipping' => 1,
        'Magento_OrderHistorySearch' => 1,
        'Magento_BannerCustomerSegment' => 1,
        'Magento_PageBuilder' => 1,
        'Magento_PageBuilderAnalytics' => 1,
        'Magento_CatalogInventoryStaging' => 1,
        'Magento_AdminGws' => 1,
        'Magento_PaymentStaging' => 1,
        'Magento_Vault' => 1,
        'Magento_Paypal' => 1,
        'Magento_PaypalGraphQl' => 1,
        'Magento_PaypalNegotiableQuote' => 1,
        'Magento_PaypalOnBoarding' => 1,
        'Magento_PurchaseOrder' => 1,
        'Magento_Persistent' => 1,
        'Magento_PersistentHistory' => 1,
        'Magento_PricePermissions' => 1,
        'Magento_DownloadableStaging' => 1,
        'Magento_ProductVideo' => 1,
        'Magento_ProductVideoStaging' => 1,
        'Magento_PromotionPermissions' => 1,
        'Magento_CheckoutAgreementsPurchaseOrder' => 1,
        'Magento_PurchaseOrderRule' => 1,
        'Magento_QuickOrder' => 1,
        'Magento_BannerGraphQl' => 1,
        'Magento_QuoteAnalytics' => 1,
        'Magento_QuoteBundleOptions' => 1,
        'Magento_QuoteConfigurableOptions' => 1,
        'Magento_QuoteDownloadableLinks' => 1,
        'Magento_QuoteGiftCardOptions' => 1,
        'Magento_BundleRequisitionListGraphQl' => 0,
        'Magento_QuoteStaging' => 1,
        'Magento_ReCaptchaAdminUi' => 1,
        'Magento_ReCaptchaCheckout' => 1,
        'Magento_ReCaptchaCompany' => 1,
        'Magento_ReCaptchaContact' => 1,
        'Magento_ReCaptchaCustomer' => 1,
        'Magento_ReCaptchaFrontendUi' => 1,
        'Magento_ReCaptchaMigration' => 1,
        'Magento_ReCaptchaNewsletter' => 1,
        'Magento_ReCaptchaPaypal' => 1,
        'Magento_ReCaptchaReview' => 1,
        'Magento_ReCaptchaSendFriend' => 1,
        'Magento_ReCaptchaStorePickup' => 1,
        'Magento_ReCaptchaUi' => 1,
        'Magento_ReCaptchaUser' => 1,
        'Magento_ReCaptchaValidation' => 1,
        'Magento_ReCaptchaValidationApi' => 1,
        'Magento_ReCaptchaVersion2Checkbox' => 1,
        'Magento_ReCaptchaVersion2Invisible' => 1,
        'Magento_ReCaptchaVersion3Invisible' => 1,
        'Magento_ReCaptchaWebapiApi' => 1,
        'Magento_ReCaptchaWebapiGraphQl' => 1,
        'Magento_ReCaptchaWebapiRest' => 1,
        'Magento_ReCaptchaWebapiUi' => 1,
        'Magento_RelatedProductGraphQl' => 1,
        'Magento_ReleaseNotification' => 1,
        'Magento_Reminder' => 1,
        'Magento_RemoteStorage' => 1,
        'Magento_RemoteStorageCommerce' => 1,
        'Magento_InventoryLowQuantityNotificationAdminUi' => 0,
        'Magento_RequireJs' => 1,
        'Magento_BundleRequisitionList' => 1,
        'Magento_ConfigurableRequisitionListGraphQl' => 0,
        'Magento_ResourceConnections' => 1,
        'Magento_Review' => 1,
        'Magento_ReviewAnalytics' => 1,
        'Magento_ReviewGraphQl' => 1,
        'Magento_ReviewStaging' => 1,
        'Magento_Reward' => 1,
        'Magento_RewardGraphQl' => 1,
        'Magento_AdvancedSalesRule' => 1,
        'Magento_Rma' => 1,
        'Magento_RmaGraphQl' => 1,
        'Magento_RmaStaging' => 1,
        'Magento_AwsS3' => 1,
        'Magento_Rss' => 1,
        'Magento_SalesRuleStaging' => 1,
        'Magento_BannerPageBuilderAnalytics' => 1,
        'Magento_SalesAnalytics' => 1,
        'Magento_SalesArchive' => 1,
        'Magento_MultipleWishlistGraphQl' => 1,
        'Magento_SalesInventory' => 1,
        'Magento_CatalogRuleStaging' => 1,
        'Magento_RewardStaging' => 1,
        'Magento_ConfigurableNegotiableQuote' => 1,
        'Magento_SampleData' => 1,
        'Magento_ScalableCheckout' => 1,
        'Magento_ScalableInventory' => 1,
        'Magento_PaypalPurchaseOrder' => 1,
        'Magento_ScheduledImportExport' => 1,
        'Magento_CatalogPermissionsGraphQl' => 1,
        'Magento_SearchStaging' => 1,
        'Magento_CompanyCredit' => 1,
        'Magento_Securitytxt' => 1,
        'Magento_SendFriend' => 1,
        'Magento_SendFriendGraphQl' => 1,
        'Magento_BundleSharedCatalog' => 1,
        'Magento_SharedCatalogGraphQl' => 0,
        'Magento_CompanyShipping' => 1,
        'Magento_AwsS3PageBuilder' => 1,
        'Magento_StagingGraphQl' => 1,
        'Magento_CatalogStagingGraphQl' => 1,
        'Magento_StagingPageBuilder' => 1,
        'Magento_CheckoutAddressSearchGiftRegistry' => 1,
        'Magento_InventoryConfigurableProductAdminUi' => 0,
        'Magento_Support' => 1,
        'Magento_Swagger' => 1,
        'Magento_SwaggerWebapi' => 1,
        'Magento_SwaggerWebapiAsync' => 1,
        'Magento_Swat' => 1,
        'Magento_Swatches' => 1,
        'Magento_SwatchesGraphQl' => 1,
        'Magento_SwatchesLayeredNavigation' => 1,
        'Magento_CatalogStagingPageBuilder' => 1,
        'Magento_TargetRuleGraphQl' => 1,
        'Magento_ConfigurableSharedCatalog' => 0,
        'Magento_TaxGraphQl' => 1,
        'Magento_TaxImportExport' => 1,
        'Magento_BannerPageBuilder' => 1,
        'Magento_ThemeGraphQl' => 1,
        'Magento_Translation' => 1,
        'Magento_TwoFactorAuth' => 0,
        'Magento_GiftCardSharedCatalog' => 0,
        'Magento_Ups' => 1,
        'Magento_CatalogUrlRewriteStaging' => 1,
        'Magento_CatalogUrlRewriteGraphQl' => 1,
        'Magento_AsynchronousOperations' => 1,
        'Magento_Usps' => 1,
        'Magento_B2b' => 1,
        'Magento_PaypalCaptcha' => 1,
        'Magento_VaultGraphQl' => 1,
        'Magento_Version' => 1,
        'Magento_CmsStaging' => 1,
        'Magento_VersionsCmsPageCache' => 1,
        'Magento_VersionsCmsUrlRewrite' => 1,
        'Magento_VersionsCmsUrlRewriteGraphQl' => 1,
        'Magento_GiftCardStaging' => 1,
        'Magento_InventoryInStorePickupWebapiExtension' => 0,
        'Magento_WebapiAsync' => 1,
        'Magento_WebapiSecurity' => 1,
        'Magento_ElasticsearchCatalogPermissions' => 1,
        'Magento_BundleStaging' => 1,
        'Magento_WeeeGraphQl' => 1,
        'Magento_WeeeStaging' => 1,
        'Magento_PageBuilderAdminAnalytics' => 1,
        'Magento_ConfigurableProductStaging' => 1,
        'Magento_WishlistAnalytics' => 1,
        'Magento_WishlistGiftCard' => 1,
        'Magento_WishlistGiftCardGraphQl' => 1,
        'Magento_GiftCardRequisitionListGraphQl' => 0,
        'Alfakher_AddtocartPriceHide' => 1,
        'Alfakher_AdminReorder' => 1,
        'Amasty_RequestQuote' => 1,
        'Magefan_Community' => 1,
        'Alfakher_CatalogExtended' => 1,
        'Alfakher_Categoryb2b' => 1,
        'Alfakher_CheckoutPage' => 1,
        'Alfakher_CmsCanonical' => 1,
        'Alfakher_CompanyImport' => 1,
        'ShipperHQ_Shipper' => 1,
        'Alfakher_Customersavepayment' => 1,
        'Alfakher_DocumentStatus' => 1,
        'Alfakher_ExciseReport' => 1,
        'Vrpayecommerce_Vrpayecommerce' => 1,
        'Alfakher_FinanceVerified' => 1,
        'Alfakher_GiftProduct' => 1,
        'MageWorx_OrdersBase' => 1,
        'Alfakher_GroupedProduct' => 1,
        'MageWorx_OrderEditor' => 1,
        'Alfakher_HwCustomerTelephoneUpdate' => 1,
        'Alfakher_KlaviyoCustomCatalog' => 1,
        'Mageplaza_Core' => 1,
        'Alfakher_MyDocument' => 1,
        'Alfakher_NotPaidInvoice' => 1,
        'Alfakher_OfflinePaymentRecords' => 1,
        'Alfakher_OrderComment' => 1,
        'Alfakher_OrderPdf' => 1,
        'Alfakher_OutOfStockProduct' => 1,
        'Alfakher_PaymentEdit' => 1,
        'Klarna_Core' => 1,
        'Alfakher_Productpageb2b' => 1,
        'Alfakher_QuoteExtended' => 1,
        'Alfakher_RequestQuote' => 1,
        'Alfakher_RmaCustomization' => 1,
        'Alfakher_SalesApprove' => 1,
        'Mageplaza_Webhook' => 1,
        'Alfakher_SeoUrlPrefix' => 1,
        'Alfakher_ShippingEdit' => 1,
        'Signifyd_Connect' => 1,
        'Alfakher_SlopePayment' => 1,
        'Alfakher_StockAlert' => 1,
        'Alfakher_StoreCredit' => 1,
        'Tabby_Checkout' => 1,
        'Alfakher_WarehouseShipping' => 1,
        'Alfakher_GrossMargin' => 1,
        'Amasty_BannersLite' => 1,
        'Amasty_Base' => 1,
        'Amasty_Conditions' => 1,
        'Amasty_Mage24Fix' => 1,
        'Amasty_Promo' => 1,
        'Alfakher_AmastyExtended' => 1,
        'Amasty_Rgrid' => 1,
        'Amasty_SalesRuleWizard' => 1,
        'Amasty_Scroll' => 1,
        'Amasty_ShopbyBase' => 1,
        'Amasty_Shopby' => 1,
        'Amasty_ShopbyBrand' => 1,
        'Amasty_ShopbyPage' => 1,
        'Amasty_ShopbySeo' => 1,
        'Amazon_Core' => 0,
        'Amazon_Login' => 0,
        'Amazon_Payment' => 0,
        'Auctane_Api' => 1,
        'Avalara_BaseProvider' => 1,
        'Avalara_Excise' => 1,
        'Corra_AmastyPromoGraphQl' => 1,
        'Corra_AttributesGraphQl' => 1,
        'Corra_CmsSitemapGraphQl' => 1,
        'Corra_LinkGuestOrder' => 1,
        'Corra_NewRelicReportingGraphql' => 1,
        'Corra_PageBuilderCustomisation' => 1,
        'Corra_PwaMaintenanceCheck' => 1,
        'Corra_QuoteBundleItemStock' => 1,
        'Corra_SignifydGraphQl' => 1,
        'Corra_Spreedly' => 1,
        'Corra_Veratad' => 1,
        'Yotpo_Loyalty' => 1,
        'Dotdigitalgroup_Email' => 1,
        'Dotdigitalgroup_Chat' => 1,
        'Dotdigitalgroup_ChatGraphQl' => 1,
        'Dotdigitalgroup_B2b' => 1,
        'Dotdigitalgroup_EmailGraphQl' => 1,
        'Dotdigitalgroup_Enterprise' => 1,
        'Dotdigitalgroup_Sms' => 1,
        'Fastly_Cdn' => 1,
        'Fooman_EmailAttachments' => 1,
        'Glew_Service' => 1,
        'GlobalHookah_SalesExtended' => 1,
        'HookahShisha_Avalara' => 1,
        'HookahShisha_AvalaraExciseGraphQl' => 1,
        'Magefan_Blog' => 1,
        'HookahShisha_Catalog' => 1,
        'HookahShisha_CatalogGraphQl' => 1,
        'HookahShisha_ChangePassword' => 1,
        'HookahShisha_Checkoutchanges' => 1,
        'HookahShisha_CustomerGraphQl' => 1,
        'HookahShisha_Customerb2b' => 1,
        'Magetrend_PdfTemplates' => 1,
        'HookahShisha_GraphQl' => 0,
        'HookahShisha_Import' => 1,
        'HookahShisha_InternationalTelephoneInput' => 1,
        'HookahShisha_InvoiceCapture' => 1,
        'HookahShisha_InvoicePdf' => 1,
        'Klarna_Ordermanagement' => 1,
        'HookahShisha_LoginAsCustomer' => 1,
        'HookahShisha_Magefan' => 1,
        'HookahShisha_Migration' => 1,
        'HookahShisha_Order' => 1,
        'HookahShisha_OrderGraphQl' => 1,
        'HookahShisha_QuoteGraphQl' => 1,
        'HookahShisha_ReCaptcha' => 1,
        'HookahShisha_Removefreegift' => 1,
        'HookahShisha_RmaGraphQl' => 1,
        'HookahShisha_Sales' => 1,
        'HookahShisha_SalesGraphQl' => 1,
        'HookahShisha_SocialLogin' => 1,
        'Magedelight_Base' => 1,
        'HookahShisha_SwatchData' => 1,
        'HookahShisha_SwatchesGraphQl' => 1,
        'Alfakher_PaymentMethod' => 1,
        'Klarna_Kp' => 1,
        'Klarna_KpGraphQl' => 1,
        'Klarna_Onsitemessaging' => 1,
        'HookahShisha_Klarna' => 1,
        'Klaviyo_Reclaim' => 1,
        'MSP_Common' => 1,
        'MSP_CmsImportExport' => 1,
        'MageModule_Core' => 1,
        'MageModule_OrderImportExport' => 1,
        'MageWorx_Info' => 1,
        'Alfakher_Webhook' => 1,
        'MageWorx_OrderEditorCustom' => 1,
        'Alfakher_HandlingFee' => 1,
        'Magedelight_Subscribenow' => 1,
        'Magedelight_SubscribenowGraphQl' => 1,
        'HookahShisha_SubscribeGraphQl' => 1,
        'Magefan_AdminUserGuide' => 1,
        'Alfakher_Blog' => 1,
        'Magefan_BlogAuthor' => 1,
        'Magefan_BlogPlus' => 1,
        'Magefan_BlogGraphQl' => 1,
        'Magefan_BlogImport' => 1,
        'Magefan_BlogExtra' => 1,
        'HookahShisha_BlogGraphQl' => 1,
        'Magefan_ProductWidget' => 1,
        'Magefan_WysiwygAdvanced' => 1,
        'Magefan_YouTubeWidget' => 1,
        'Mageplaza_MultipleCoupons' => 1,
        'Alfakher_MultipleCoupons' => 1,
        'Alfakher_ProductLabels' => 1,
        'Mageplaza_ProductLabels' => 1,
        'Mageplaza_ProductLabelsGraphQl' => 1,
        'Mageplaza_Seo' => 1,
        'Mageplaza_Sitemap' => 1,
        'Alfakher_ReviewFix' => 1,
        'Alfakher_Seamlesschex' => 1,
        'MagestyApps_WebImages' => 1,
        'HookahShisha_Customization' => 1,
        'Mondu_Mondu' => 1,
        'OlegKoval_RegenerateUrlRewrites' => 1,
        'Ooka_Catalog' => 1,
        'Ooka_Customizations' => 1,
        'Ooka_OokaSerialNumber' => 1,
        'PCAPredict_Tag' => 1,
        'ParadoxLabs_TokenBase' => 1,
        'ParadoxLabs_FirstData' => 1,
        'PayPal_Braintree' => 0,
        'PayPal_BraintreeGraphQl' => 0,
        'PluginCompany_LicenseManager' => 1,
        'PluginCompany_CmsRevisions' => 1,
        'RedChamps_Core' => 1,
        'RedChamps_EmailAttachmentHelper' => 1,
        'RedChamps_UnpaidInvoices' => 1,
        'SetuBridge_ChangeCustomerpwbyadmin' => 1,
        'ShipperHQ_Common' => 1,
        'ShipperHQ_Logger' => 1,
        'Alfakher_CustomerCourierAccount' => 1,
        'Shishaworld_GraphQlTranslation' => 1,
        'Shishaworld_OrderItemExcludingTaxPrice' => 1,
        'Alfakher_SignifydHoldOrder' => 1,
        'Smartwave_Megamenu' => 1,
        'SouthSmoke_AdditionalCharges' => 1,
        'Splitit_PaymentGateway' => 1,
        'Alfakher_Tabby' => 1,
        'Temando_ShippingRemover' => 1,
        'Vertex_Tax' => 1,
        'Vertex_AddressValidationApi' => 1,
        'Vertex_RequestLoggingApi' => 1,
        'Vertex_RequestLogging' => 1,
        'Vertex_AddressValidation' => 1,
        'Vertex_TaxStaging' => 1,
        'Vrpayecommerce_VrpayecommerceGraphql' => 1,
        'Alfakher_ExitB' => 1,
        'Wyomind_Framework' => 1,
        'Wyomind_MassStockUpdate' => 1,
        'Wyomind_MassProductImport' => 1,
        'Xtento_XtCore' => 1,
        'Xtento_OrderImport' => 1,
        'Corra_YotpoLoyaltyExtended' => 1,
        'Yotpo_Yotpo' => 1,
        'Zoho_Salesiq' => 1,
        'eDevice_IpRecognitionPopup' => 1
    ],
    'admin_user' => [
        'locale' => [
            'code' => [
                'en_US'
            ]
        ]
    ],
    'themes' => [
        'frontend/Magento/blank' => [
            'parent_id' => null,
            'theme_path' => 'Magento/blank',
            'theme_title' => 'Magento Blank',
            'is_featured' => '0',
            'area' => 'frontend',
            'type' => '0',
            'code' => 'Magento/blank'
        ],
        'adminhtml/Magento/backend' => [
            'parent_id' => null,
            'theme_path' => 'Magento/backend',
            'theme_title' => 'Magento 2 backend',
            'is_featured' => '0',
            'area' => 'adminhtml',
            'type' => '0',
            'code' => 'Magento/backend'
        ],
        'frontend/Magento/luma' => [
            'parent_id' => 'Magento/blank',
            'theme_path' => 'Magento/luma',
            'theme_title' => 'Magento Luma',
            'is_featured' => '0',
            'area' => 'frontend',
            'type' => '0',
            'code' => 'Magento/luma'
        ],
        'frontend/Corra/alfakher' => [
            'parent_id' => 'Magento/luma',
            'theme_path' => 'Corra/alfakher',
            'theme_title' => 'Al Fakher',
            'is_featured' => '0',
            'area' => 'frontend',
            'type' => '0',
            'code' => 'Corra/alfakher'
        ],
        'frontend/Alfakher/hookahwholesalers' => [
            'parent_id' => 'Magento/blank',
            'theme_path' => 'Alfakher/hookahwholesalers',
            'theme_title' => 'Hookah wholesalers',
            'is_featured' => '0',
            'area' => 'frontend',
            'type' => '0',
            'code' => 'Alfakher/hookahwholesalers'
        ],
        'frontend/Corra/ooka' => [
            'parent_id' => 'Magento/luma',
            'theme_path' => 'Corra/ooka',
            'theme_title' => 'Ooka',
            'is_featured' => '0',
            'area' => 'frontend',
            'type' => '1',
            'code' => 'Corra/ooka'
        ],
        'frontend/Corra/ooka-de' => [
            'parent_id' => 'Magento/luma',
            'theme_path' => 'Corra/ooka-de',
            'theme_title' => 'Ooka DE',
            'is_featured' => '0',
            'area' => 'frontend',
            'type' => '1',
            'code' => 'Corra/ooka-de'
        ],
        'frontend/Alfakher/shishaworld' => [
            'parent_id' => 'Alfakher/hookahwholesalers',
            'theme_path' => 'Alfakher/shishaworld',
            'theme_title' => 'Shisha World B2B',
            'is_featured' => '0',
            'area' => 'frontend',
            'type' => '0',
            'code' => 'Alfakher/shishaworld'
        ],
        'frontend/Alfakher/globalhookah' => [
            'parent_id' => 'Alfakher/hookahwholesalers',
            'theme_path' => 'Alfakher/globalhookah',
            'theme_title' => 'Global Hookah B2B',
            'is_featured' => '0',
            'area' => 'frontend',
            'type' => '0',
            'code' => 'Alfakher/globalhookah'
        ]
    ],
    'i18n' => [

    ]
];
