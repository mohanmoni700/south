define(["jquery", "jquery/ui", 'mage/url'], function($, ui, urlBuilder) {
    "use strict";

    function main(config, element) {
        $(document).ready(function() {
            $(config.passwordSelector).show();
            $(config.email).focus();
            $(config.submitButton).text("sign in");
            $(config.submitButton).prop("disabled", false);
        });

        $("#login-form").submit(function(e) {
            e.preventDefault();
            var form = $('form#login-form');
            if (form.validation('isValid')) {
                var form_data = jQuery("#login-form").serialize();
                $.ajax({
                    type: "POST",
                    showLoader: true,
                    url: config.loginUrl,
                    data: form_data,
                }).done(function(msg) {
                    if (msg.migrate_customer == 1) {
                        var headerTextValue=config.getResetHeaderMessageConfig;
                        $(config.passwordSelector).hide();
                        $("#migrate-customer-header-text").html(headerTextValue);
                        $("#block-customer-login-heading").hide();
                        $(config.submitButton).text("Reset Password");
                        $(config.submitButton).prop("disabled", false);
                        $('#migrate_customer').val(1);
                    }  else if (msg.url) {
                        var url = urlBuilder.build(msg.url);
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