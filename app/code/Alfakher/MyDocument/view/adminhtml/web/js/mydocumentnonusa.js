define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/calendar',
    'mage/mage',
    'mage/validation'
], function($, modal) {
    'use strict';
    return function(config) {

        /** File Preview
         *
         * @param {String} showId
         * @param {String} docActionInsertAfter
         * @param {Object} currentElem
         * @param {Object} e
         */
        function fileRender(showId, docActionInsertAfter,currentElem,e) {
            var val = currentElem.val();
            if (val) {
                var extension = val.substring(val.lastIndexOf('.') + 1).toLowerCase();
                var extensionArr = ['pdf', 'jpg', 'png'];
                if (extensionArr.indexOf(extension) >= 0) {
                    currentElem.closest('.upload').find('.doc_name').remove();
                    currentElem.closest('.upload').find('.set_expiry').remove();
                    var filesrc = URL.createObjectURL(e.target.files[0]);
                    var filename = jQuery('#'+ docActionInsertAfter + showId).val();
                    var nameArr = filename.split('\\');
                    var name = nameArr[2];
                    $('<div class="comman-doc-name document'+ showId + '">' +
                        '<span>'+name+'</span>'+
                        '<a class="comman-download-doc" id="downloaddocument-filename'+showId+'" href="' + filesrc + '" target="_blank" download></a>' +
                        '</div>'+'<div class="doc_name" for="document name">' +
                        '<label>Document Name</label>' +
                        '<input type="text" id="name' + showId + '" name="name' + showId + '" class="required-entry required">' +
                        '</div><div class="set_expiry" data-id=' + showId + ' for="document expiry data">' +
                        '<label>Set a Specific Expiry date</label>' +
                        '<input type="checkbox" name="set_expiry-' + showId + '" id="toggle-' + showId + '" value="0" class="cmn-toggle cmn-toggle-round">' +
                        '<span class="slider round"></span>' +
                        '<div class="expiry_dates expiry_date-' + showId + '" for="document expiry data" style="display:none" >' +
                        '<label>Set Expiry Date</label>' +
                        '<input type="text" placeholder="Expiry Date" name="expiry_date'+ showId +'" id="expiry_date-' + showId + '" class="required-entry required" readonly>' +
                        '</div></div>').insertAfter(currentElem.closest('.upload .input-file'));
                    $("#image-error-message-" + showId).html("");
                    $("#filename-" + showId + "-error").remove();

                    $("#doc-actions-filename-" + showId).remove();
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        if (event.target.result) {
                            var filename = $('#'+ docActionInsertAfter + showId).val();
                            var extension = filename.replace(/^.*\./, '');
                            var nameArr = filename.split('\\');
                            if (extension == 'pdf') {
                                var filePreview = config.pdfImg;
                                var filesrc = URL.createObjectURL(e.target.files[0]);
                                var imgClass = "pdf-image";
                            } else {
                                var filePreview = URL.createObjectURL(e.target.files[0]);
                                var filesrc = URL.createObjectURL(e.target.files[0]);
                                var imgClass = "";
                            }

                            $('<img class="previewimage-filename-' + showId + ' ' + imgClass + '" height="170" width="170" src="' + filePreview + '" title="' + filename + '"/>' +
                                '<div class="doc-actions" id="doc-actions-filename-' + showId + '">' +
                                '<a class="view-doc-link" id="view-doc-link-filename-' + showId + '" href="' + filesrc + '" target="_blank"></a>' +
                                '<a class="deletedocument-preview deletedocument" id="deletedocument-filename-' + showId + '"></a>' +
                                '</div>').insertAfter($('#' + docActionInsertAfter + showId));

                            $("#deletedocument-filename-" + showId).click(function() {
                                $('.previewimage-filename-' + showId).remove();
                                $("#doc-actions-filename-" + showId).remove();
                                $('.document'+ showId).remove();
                                $('#' + docActionInsertAfter + showId).val('');
                                currentElem.closest('.upload').removeClass('active');
                            });
                        }
                    }
                    reader.readAsDataURL(e.target.files[0]);
                    currentElem.closest('.upload').addClass('active');
                } else {
                    currentElem.val('');
                    currentElem.closest('.upload').removeClass('active');
                    $("#image-error-message-" + showId).html("File type supported are JPG,PNG and PDF Only");
                    return false;
                }

                $(document).on('change', "#toggle-" + showId, function() {
                    this.value = this.checked ? 1 : 0;
                    if ($(this).val() == "1") {
                        $(".expiry_date-" + showId).show();
                        fnDatePicker(showId);
                    } else {
                        $(".expiry_date-" + showId).hide();
                    }
                });
                expireOnChange(showId);
                $("#nonusabtnsave").show();
            } else {
                currentElem.closest('.upload').removeClass('active');
                $("#nonusabtnsave").hide();
            }
        }

        /** Expiry Date Picker
         *
         * @param {String} showId
         */
        function fnDatePicker(showId) {
            $("#expiry_date-" + showId).datepicker({
                minDate: 1,
                showMonthAfterYear: false,
                dateFormat: 'mm/dd/yy',
                changeMonth: true,
                changeYear: true,
                yearRange: '2020:2030',
            });
        }

        /** Expiry Date On Change
         *
         * @param {String} showId
         */
        function expireOnChange(showId) {
            $(document).on("change", "#expiry_date-" + showId, function(e){
                if ($(this).val()) {
                    var date_val = $(this).val().indexOf('Expiry Date: ') == -1 ? $(this).val() : $(this).val().split('Expiry Date: ')[1];
                    $(this).val('Expiry Date: ' + date_val);
                    $(this).addClass('active-date');
                }
            });
        }

        /** Form submit action by ID
         *
         * @param {String} id
         * @param {String} resetId
         * @param {String} postUrl
         */
        function formSubmitById(id, resetId, postUrl) {
            $(document).on("submit", "form#"+id, function(e){
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: postUrl,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    showLoader: true,
                    success: function(data) {
                        if (data.success == 1) {
                            if(data.tab !== undefined && data.tab != ''){

                                var Url = window.location.href+"active_tab/3/";
                                var Url = String( Url ).replace( "#", "" );
                                window.location.href = Url;

                            }else{
                                window.location.reload(true);
                            }
                            
                        } else {
                            $(".page.messages").html('<div role="alert" class="messages">' +
                                '<div class="alert danger alert-danger" data-ui-id="message-danger">' +
                                '<div>Data could not saved !.</div>' +
                                '</div></div>');
                            $(".page.messages").delay(200).fadeIn().delay(4000).fadeOut();
                        }
                    }
                });
            });
        }

        $(document).ready(function() {
            expireOnChange(1);
            $(document).on("change", '#toggle', function(e){
                this.value = this.checked ? 1 : 0;
                if ($(this).val() == "1") {
                    $("#expiry_date").parent().show();
                    //$("#expiry_date").attr("data-validate", "{required:true}");
                    $("#expiry_date").addClass("required-entry required");
                } else {
                    $("#expiry_date").parent().hide();
                }
                fnDatePicker(1);
            }).change();

            $(document).on("change", '#filename-1', function(e){
                fileRender(1, 'filename-',$(this),e);
            });

            var id = 1;
            $(document).on("click", '#add_more_non_usa', function(e){
                var id = jQuery('#myformdynamic').find('input[type="file"]').length;
                var showId = ++id;
                if (showId <= 25) {
                    $('<div class="upload" id="delete-uploaded'+showId+'" for="document uploader">' +
                        '<div class="input-file">' +
                        '<input type="file" id="filename-' + showId + '" class="required-entry required upload-filename" name="filename' + showId + '" class="required-entry required">' +
                        '<span id="image-error-message-'+ showId + '" style="color:red;"></span>'+
                        '<span class="input-note">' + config.inputNote + '</span>' +
                        '<input type="hidden" id="is_add_more_form' + showId + '" name="is_add_more_form[]" value="1">' +
                        '</div>' + '<div class="del" id="delete">' +
                        '<a class="delete-icon" id="del-button' + showId + '" href="#">delete</a>' +
                        '</div>' + '</div>').insertBefore('.upload-container .add-more-cont');
                }
                $("#del-button" + showId).click(function() {
                    $("#delete-uploaded"+showId).hide();
                    $("#name" + showId).val('');
                    
                });
                if (showId == 25){
                    $(".add-more-cont").hide();
                    $("#add_more_non_usa").hide();
                }
                $(document).on("change", '#filename-' + showId, function(e){
                //$('#filename-' + showId).change(function(e) {
                    fileRender(showId, 'filename-',$(this),e);
                });
            });

            
            $(document).on("click", 'button#nonusabtnsave', function(e){
                var dataForm = $('#myformdynamic');
                if($(dataForm).valid()) {
                    formSubmitById('myformdynamic', 'myformdynamic', config.customResultUrl);
                }
            });
        });
    };
});