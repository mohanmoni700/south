define([
    'Magento_Ui/js/grid/columns/multiselect'
], function (Component) {
    'use strict';

    /**
     * this component exists for the purpose of keeping track of ALL selections so
     * that the grid can be a part of the actual form
     */
    return Component.extend({
        defaults: {
            allIds: []
        },
        selectAll: function() {
            this._super();
            this.allIds.forEach(function(value) {
                this.selected.push(value);
            }.bind(this));

            return this;
        },
        deselectAll: function() {
            this._super();
            this.allIds.forEach(function(value) {
                this.excluded.push(value);
            }.bind(this));

            return this;
        }
    });
});
