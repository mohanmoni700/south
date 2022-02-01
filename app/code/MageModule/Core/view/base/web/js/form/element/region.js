/*
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 *  @author        MageModule admin@magemodule.com
 *  @copyright    2018 MageModule, LLC
 *  @license        https://www.magemodule.com/end-user-license-agreement/
 */

define([
    'Magento_Ui/js/form/element/region',
    'uiRegistry'
], function(Region, registry) {
    'use strict';

    return Region.extend({
        getCountryIdField: function() {
            return registry.get(this.parentName + '.' + this.countryField);
        },
        // region text input
        getRegionField: function() {
            var regionField = registry.get(this.parentName + '.' + this.regionField);
            if(!regionField) {
                var parts = this.parentName.split('.');
                parts.pop();

                var parent = parts.join('.') + '.container_' + this.regionField;
                regionField = registry.get(parent + '.' + this.regionField);
            }

            return regionField;
        },
        resolveParentName: function() {
            var fieldname = this.countryField,
                parentName = this.parentName,
                country = registry.get(this.parentName + '.' + fieldname),
                parts,
                newParts,
                i;

            if(!country) {
                parts = this.parentName.split('.');
                for(i = 0; i < 2; i++) {
                    parts.pop();

                    newParts = parts.join('.');
                    if(registry.get(newParts + '.container_' + fieldname)) {
                        parentName = newParts + '.container_' + fieldname;
                        break;
                    }
                }
            }

            return parentName;
        },
        update: function(value) {
            this.toggleInput(false);
        },
        filter: function(value, field) {
            var parentName = this.resolveParentName();
            if(parentName) {
                this.parentName = parentName;
            }

            this._super(value, field);

            var region = this.getRegionField();
            var visible = !!this.size(this.indexedOptions);

            this.setVisible(visible);
            region.setVisible(!visible);
        },
        size: function(object) {
            var size = 0, key;
            if (typeof object != 'undefined') {
                for(key in object) {
                    if(object.hasOwnProperty(key)) size++;
                }
            }

            return size;
        }
    });
});
