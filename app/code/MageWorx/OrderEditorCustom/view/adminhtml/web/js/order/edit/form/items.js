define(
    [
        'jquery',
        'mage/translate',
        'MageWorx_OrderEditor/js/order/edit/form/base',
        'MageWorx_OrderEditor/js/order/edit/form/items/form',
        'jquery/ui'
    ],
    function ($, $t, base, form) {
        'use strict';

        let widgetMixin = {
            _initEditLink: function () {
                let linkTemplate = this.editLinkTemplate;
                let editLink = linkTemplate.replace('%block_id%', this.params.editLinkId.substring(1));
                let head = $(this.params.formContainerId).parent().children(this.params.linkContainerId);

                head.ready(() => {
                    $(editLink).appendTo($(this.params.formContainerId).parent().children(this.params.linkContainerId));
                })
            }
        };

        return function (parentWidget) {
            $.widget('mage.mageworxOrderEditorItems', parentWidget, widgetMixin);
            return $.mage.mageworxOrderEditorItems;
        };
    }
);
