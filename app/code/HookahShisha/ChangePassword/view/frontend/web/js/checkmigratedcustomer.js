define([
    'jquery',
    'mage/url',
    'mage/validation'
], function($, urlBuilder) {
    'use strict';

    return function(config) {

        $(document).ready(function() {
            
            $(config.passwordSelector).hide();

            function validateEmail(email) {
                var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if(!regex.test(email)) {
                    return false;
                }else{
                    return true;
                }
            }

            $(config.email).bind('change',function(e) {
                var email_login = $(config.email).val();
                var status = validateEmail(email_login);
                if(status === true) {
                    e.preventDefault();
                    $.ajax({
                        type: "POST",
                        showLoader: true,
                        url: config.checkmigratedcustomerurl,
                        data: {email : email_login},
                        dataType: "json"
                    })
                    .done(function (msg) {
                        if(msg.errors) {
                            $('.response-msg').html("<div class='error'>"+msg.message+"</div>");
                            setTimeout(function(){ $('.response-msg').html(null); }, 5000);
                        } else {
                            if (msg.migrate_customer == 1){
                                $(config.passwordSelector).hide();
                                $(config.submitButton).text("Reset Password");
                                $(config.submitButton).prop("disabled",false);
                            }else{
                                $(config.passwordSelector).show();
                                $(config.submitButton).text("sign in");
                                $(config.submitButton).prop("disabled",false);
                            }
                        }

                    });
                }
            });
        });
    };
});