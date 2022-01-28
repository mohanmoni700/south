/*
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 *  @author        MageModule admin@magemodule.com
 *  @copyright    2018 MageModule, LLC
 *  @license        https://www.magemodule.com/magento2-ext-license.html
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'mage/translate',
    'jquery',
    'jquery/ui'
], function(Component, $t, $) {
    'use strict';

    return Component.extend({
        options: {},
        timepicker: null,
        initTimepicker: function(element) {
            this.timepicker = $(element).timepicker(this.options);
            this.timepicker.next('.ui-datepicker-trigger')
                .addClass('v-middle')
                .text('');
        },
        openTimepicker: function() {
            if (this.timepicker) {
                this.timepicker.timepicker('show');
            }
        }
    });
});
