define(["jquery", "jquery/ui", 'mage/url'], function($, ui, urlBuilder) {
    "use strict";

    function main(config, element) {
        $(document).ready(function() {
            var $element = $(element);
            $(config.passwordSelector).show();
            $(config.email).focus();
            $(config.submitButton).text("sign in");
            $(config.submitButton).prop("disabled", false);
        });

        $("#login-form").submit(function(e) {
            e.preventDefault();
            var form = $('form#login-form');
            if (form.validation('isValid')) {
                var email_login = $(config.email).val();
                var form_data = jQuery("#login-form").serialize();
                var formData = new FormData(this);
                $.ajax({
                    type: "POST",
                    showLoader: true,
                    url: config.loginUrl,
                    data: form_data,
                }).done(function(msg) {
                    /* console.log(msg);*/
                    /*location.reload();*/
                    if (msg.migrate_customer == 1) {
                        $(config.passwordSelector).hide();
                        $(config.submitButton).text("Reset Password");
                        $(config.submitButton).prop("disabled", false);
                        $('#migrate_customer').val(1);
                    } else if (msg.documentredirect) {
                        var url = urlBuilder.build(msg.documentredirect);
                        window.location.href = url;
                    } else if (msg.homeredirect) {
                        var url = urlBuilder.build(msg.homeredirect);
                        window.location.href = url;
                    } else {
                        location.reload();
                    }
                });
            }
        });
    };
    return main;
});