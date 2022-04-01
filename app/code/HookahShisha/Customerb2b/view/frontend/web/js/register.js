define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'mage/mage'
], function($,modal,alert) {
    'use strict';
    return function(config) {
        function onCountryChange() {
            var countryval = $('#country option:selected').val();
            if (countryval == 'US') {
                $('#region_id_label').children('span').html(config.regionStateLabel);
                $('#region_id').attr('placeholder', config.regionStateLabel);
                $('.vat-tax-id').show();
                $('.tin-number').show();
                $('.tobacco-permit-number').show();
            } else {
                $('#region_id_label').children('span').html(config.regionProvinceLabel);
                $('#region').attr('placeholder', config.regionProvinceLabel);
                $('.vat-tax-id').hide();
                $('#vat_tax_id').val('');
                $('.tin-number').hide();
                $('#tin_number').val('');
                $('.tobacco-permit-number').hide();
                $('#tobacco_permit_number').val('');
            }
        }

        function nextPage() {
            var dataForm = $('#form-validate');
            dataForm.mage('validation', {});
            var status = dataForm.validation('isValid');

            if (status) {
                $('input[name="company[company_email]"]').val($('input[name="email"]').val());
                if ($("#basic_details").hasClass("show")) {
                    $("#basic_details").hide();
                    $("#basic_details").removeClass("show");
                    $(".basic_details").removeClass("active");

                    $("#business_details").show();
                    $("#business_details").addClass("show");
                    $(".business_details").addClass("active");
                }
                onCountryChange();
                window.scrollTo(0, 200);
            }
        }

        function prevPage() {
            if ($("#business_details").hasClass("show")) {
                $("#business_details").hide();
                $("#business_details").removeClass("show");
                $(".business_details").removeClass("active");

                $("#basic_details").show();
                $("#basic_details").addClass("show");
                $(".basic_details").addClass("active");
                window.scrollTo(0, 200);
            }
        }
        $(document).ready(function() {

            $("#country").change(function() {
                onCountryChange();
            });
            $("#next").click(function() {
                nextPage();
            });
            $("#previous").click(function() {
                prevPage();
            });

            $("#signinredirect, #nonusadocskip, #usadocskip").click(function(){
                window.location.href = config.signInUrl;
            });

            $("#form-validate").submit(function(event) {
                var dataForm = $('#form-validate');
                dataForm.mage('validation', {});
                var status = dataForm.validation('isValid');

                if (status) {
                    event.preventDefault();
                    var formData = new FormData(this);
                    $.ajax({
                        url: config.createPostUrl,
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        showLoader: true,
                        success: function(data) {
                            if (data.status == 'success') {
                                var options = {
                                    type: 'popup',
                                    responsive: true,
                                    innerScroll: true,
                                    modalClass: 'document-modal',
                                    clickableOverlay: false,
                                    buttons: [{
                                        text: $.mage.__('Continue'),
                                        class: 'action primary',
                                        click: function() {
                                            this.closeModal();
                                            window.location.href = config.homeUrl;

                                            /*$("#basic_details").hide();
                                            $("#basic_details").removeClass("show");
                                            $(".basic_details").removeClass("active");

                                            $("#business_details").hide();
                                            $("#business_details").removeClass("show");
                                            $(".business_details").removeClass("active");

                                            $("#document_upload").show();
                                            $("#document_upload").addClass("show");
                                            $(".document_upload").addClass("active");
                                            $('#form-validate').trigger("reset");
                                            if(data.country == "US")
                                            {
                                                $('#cust_id_usa').val(data.id);
                                                $("#usa_customer_tab").show();
                                            }
                                            if (data.country != "US")
                                            {
                                                $('#cust_id_non_usa').val(data.id);
                                                $("#non_usa_customer_tab").show();
                                            }*/
                                        }
                                    },
/*                                    {
                                    class: 'action-close',
                                    click: function() {
                                        this.closeModal();
                                        $("#basic_details").hide();
                                        $("#basic_details").removeClass("show");
                                        $(".basic_details").removeClass("active");

                                        $("#business_details").hide();
                                        $("#business_details").removeClass("show");
                                        $(".business_details").removeClass("active");

                                        $("#document_upload").show();
                                        $("#document_upload").addClass("show");
                                        $(".document_upload").addClass("active");
                                        $('#form-validate').trigger("reset");
                                        if(data.country == "US")
                                        {
                                            $('#cust_id_usa').val(data.id);
                                            $("#usa_customer_tab").show();
                                        }
                                        if (data.country != "US")
                                        {
                                            $('#cust_id_non_usa').val(data.id);
                                            $("#non_usa_customer_tab").show();
                                        }  
                                    }
                                }*/
                                ]
                                };

                                // Reload the page on Esc key
/*                                $(document).keydown(function(event) {
                                    if (event.keyCode == 27) { 
                                        $("#basic_details").hide();
                                        $("#basic_details").removeClass("show");
                                        $(".basic_details").removeClass("active");

                                        $("#business_details").hide();
                                        $("#business_details").removeClass("show");
                                        $(".business_details").removeClass("active");

                                        $("#document_upload").show();
                                        $("#document_upload").addClass("show");
                                        $(".document_upload").addClass("active");
                                        $('#form-validate').trigger("reset");
                                        if(data.country == "US")
                                        {
                                            $('#cust_id_usa').val(data.id);
                                            $("#usa_customer_tab").show();
                                        }
                                        if (data.country != "US")
                                        {
                                            $('#cust_id_non_usa').val(data.id);
                                            $("#non_usa_customer_tab").show();
                                        }
                                    }
                                });*/
                                var popup = modal(options, $('#register-success-popup'));

                                $('#form-validate').trigger("reset");
                                $("#register-success-popup").modal("openModal");

                            } else {
                                /* Added the Alert popup for the display invalid data or error */
                                alert({
                                    title: $.mage.__(data.message),
                                    content: $.mage.__(''),
                                    modalClass: 'register-action-error',
                                    actions: {
                                        confirm: function(){
                                            $('#form-validate .action.register.submit.primary').prop("disabled",false);
                                        },
                                        cancel: function(){
                                            $('#form-validate .action.register.submit.primary').prop("disabled",false);
                                        },
                                        always: function(){}
                                    }
                                });
                                $(".page.messages").html('<div role="alert" class="messages">' +
                                    '<div class="alert danger alert-danger" data-ui-id="message-danger">' +
                                    '<div>Data could not saved !.</div>' +
                                    '</div></div>');
                                $(".page.messages").delay(200).fadeIn().delay(4000).fadeOut();
                            }
                        }
                    });
                } else {
                    $("body").trigger('processStop');
                    event.preventDefault();
                }
                // ("form#" + id).submit(function(e) {
            });
        });
    }
});