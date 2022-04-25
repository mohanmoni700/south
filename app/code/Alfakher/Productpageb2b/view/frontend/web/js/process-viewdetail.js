define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    return function (config) {
        $(function () {
            $('.view-details-actions a').click(function (event) {
                var anchor, addViewdetailsBlock;

                event.preventDefault();
                anchor = $(this).attr('href').replace(/^.*?(#|$)/, '');
                addViewdetailsBlock = $('#' + anchor);

                if (addViewdetailsBlock.length) {
                    $('.product.data.items [data-role="content"]').each(function (index) {
                        if (this.id == 'tab-viewdetails') {
                            $('.product.data.items').tabs('activate', index);
                        }
                    });
                    $('html, body').animate({
                        scrollTop: addViewdetailsBlock.offset().top - 50
                    }, 300);
                }

            });
            $('.confirm-click-here').click(function (event) {
                event.preventDefault();

                var clickHereAlert = config.clickHereAlertString, anchor;
                anchor = $(this).attr('href');

                alert({
                    title: '',
                    content: '<div class="click-here-modal-content"><span>'+clickHereAlert+'</span></div>',
                    modalClass: 'click-here-alert',
                    actions: {
                        always: function() {}
                    },
                    buttons: [{
                        text: $.mage.__('Yes'),
                        class: 'action primary accept',
                        click: function () {
                            window.location.replace(anchor);
                        }
                    }, {
                        text: $.mage.__('No'),
                        class: 'action',
                        click: function () {
                            this.closeModal(true);
                        }
                    }]
                });
            });
        });
    };
});
