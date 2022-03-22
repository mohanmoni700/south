/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MultiQuickbooksConnect
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    'jquery',
], function ($) {
    'use strict';
    $.widget('mage.orderExportScript', {
        _create: function () {
            $("#quickbooks_map_order_massaction-select").removeClass("required-entry local-validation");
        }
    });
    return $.mage.orderExportScript;
});

