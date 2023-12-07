define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/template',
], function ($, confirm, mageTemplate) {
    return function (selector, confirmationMessage) {
        $(selector).click(function (e) {
            e.stopPropagation();
            e.preventDefault();
            confirm({
                content: $.mage.__(confirmationMessage),
                modalClass: 'confirm',
                actions: {
                    confirm: function () {
                        let params = $(e.currentTarget).data('post');
                        let formTemplate = '<form action="<%- data.action %>" method="post">'
                            + '<% _.each(data.data, function(value, index) { %>'
                            + '<input name="<%- index %>" value="<%- value %>">'
                            + '<% }) %></form>';
    
                        let formKeyInputSelector = 'input[name="form_key"]';
    
                        let formKey = $(formKeyInputSelector).val();
                        if (formKey) {
                            params.data.form_key = formKey;
                        }
                        $(mageTemplate(formTemplate, {
                            data: params
                        })).appendTo('body').hide().submit();
                    }
                },
            });
        });
    };
});
