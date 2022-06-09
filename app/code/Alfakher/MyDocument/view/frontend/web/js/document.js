define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/calendar',
    'mage/validation',
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
                        '<a class="comman-download-doc" id="downloaddocument-filename-'+showId+'" href="' + filesrc + '" target="_blank" download></a>' +
                        '</div>'+'<div class="doc_name" for="document name">' +
                        '<label>Document Name</label>' +
                        '<input type="text" id="name' + showId + '" name="name' + showId + '" data-validate="{required:true}"/>' +
                        '</div><div class="set_expiry" data-id=' + showId + ' for="document expiry data">' +
                        '<label>Set a Specific Expiry date</label>' +
                        '<input type="checkbox" name="set_expiry-' + showId + '" id="toggle-' + showId + '" value="0" class="cmn-toggle cmn-toggle-round" />' +
                        '<span class="slider round"></span>' +
                        '<div class="expiry_dates expiry_date-' + showId + '" for="document expiry data" style="display:none" >' +
                        '<label>Set Expiry Date</label>' +
                        '<input type="text" placeholder="Expiry Date" name="expiry_date'+ showId +'" id="expiry_date-' + showId + '" data-validate="{required:true}" readonly/>' +
                        '</div></div>').insertAfter(currentElem.closest('.upload .input-file'));
                    $("#image-error-message-" + showId).html("");
                    $("#filename-" + showId + "-error").remove();

                    $("#doc-actions-filename-" + showId).remove();
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        if (event.target.result) {
                            var filename = jQuery('#'+ docActionInsertAfter + showId).val();
                            var extension = filename.replace(/^.*\./, '');
                            var nameArr = filename.split('\\');
                            if (extension == 'pdf') {
                                var filePreview = config.pdfImg;
                                var filesrc = URL.createObjectURL(e.target.files[0])
                                var imgClass = "pdf-image";
                            } else {
                                var filePreview = URL.createObjectURL(e.target.files[0]);
                                var filesrc = URL.createObjectURL(e.target.files[0]);
                                var imgClass = "";
                            }
                            
                            //added pdf class[BS]
                            $('<img class="previewimage-filename-' + showId + ' ' + imgClass + '" height="170" width="170" src="' + filePreview + '" title="' + filename + '"/>' +
                                '<div class="doc-actions" id="doc-actions-filename-' + showId + '">' +
                                '<a class="view-doc-link" id="view-doc-link-filename-' + showId + '" href="' + filesrc + '" target="_blank"></a>' +
                                '<a class="deletedocument-preview deletedocument" id="deletedocument-filename-' + showId + '"></a>' +
                                '</div>').insertAfter($('#' + docActionInsertAfter + showId));

                            $("#deletedocument-filename-" + showId).click(function() {
                                $('.previewimage-filename-' + showId).remove();
                                $('.document'+ showId).remove();
                                $("#doc-actions-filename-" + showId).remove();
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
                $("#btnsave, #updatebtnsave").show();
            } else {
                currentElem.closest('.upload').removeClass('active');
                $("#btnsave, #updatebtnsave").hide();
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
            $("#expiry_date-" + showId).on('change', function() {
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
            $("form#" + id).submit(function(e) {
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
                            $('#' + resetId).trigger("reset");
                            $("#popup-modal").modal("openModal");

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
            $('#toggle').on('change', function() {
                this.value = this.checked ? 1 : 0;
                if ($(this).val() == "1") {
                    $("#expiry_date").parent().show();
                    $("#expiry_date").attr("data-validate", "{required:true}");
                } else {
                    $("#expiry_date").parent().hide();
                }
                fnDatePicker(1);
            }).change();

            $('#filename-1').change(function(e) {
                fileRender(1, 'filename-',$(this),e);
            });

            var id = 1;
            $("#add_more").click(function() {
                var showId = ++id;
                if (showId <= 25) {
                    $('<div class="upload" id="delete-uploaded'+showId+'" for="document uploader">' +
                        '<div class="input-file">' +
                        '<input type="file" id="filename-' + showId + '" class="required-entry required upload-filename" name="filename' + showId + '" />' +
                        '<span id="image-error-message-'+ showId + '" style="color:red;"></span>'+
                        '<span class="input-note">' + config.inputNote + '</span>' +
                        '<input type="hidden" id="is_add_more_form' + showId + ' ?>" name="is_add_more_form[]" value="0"/>' +
                        '</div>'+ '<div class="del" id="delete">' +
                        '<a class="delete-icon" id="del-button' + showId + '" href="#">delete</a>' +
                        '</div>'+'</div>').insertBefore('.upload-container .add-more-cont');
                }
                $("#del-button" + showId).click(function() {
                    $("#delete-uploaded"+showId).hide();
                    $("#name" + showId).val('');
                    
                });

                if (showId == 25){
                    $(".add-more-cont").hide();
                    $("#add_more").hide();
                }
                $('#filename-' + showId).change(function(e) {
                    fileRender(showId, 'filename-',$(this),e);
                });
            });
            
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                modalClass: 'document-modal',
                clickableOverlay: false,
                buttons: [{
                    text: $.mage.__('Continue'),
                    class: 'mymodal1',
                    click: function() {
                        this.closeModal();
                        location.reload();
                        $('#myformdynamic').trigger("reset");  
                    }
                },{
                    class: 'action-close',
                    click: function() {
                        this.closeModal();
                        location.reload();
                        $('#myformdynamic').trigger("reset");  
                    }
                }]
            };
            
            // Reload the page on Esc key
            $(document).keydown(function(event) {
                if (event.keyCode == 27) { 
                    location.reload();
                }
            });    

            var popup = modal(options, $('#popup-modal'));
            $('#popup-modal').modal({
                closed: function (){
                    this.closeModal();
                    location.reload();
                    $('#myformdynamic').trigger("reset");
                }
            });
            $('button#btnsave').click( function(e) {
                if($('#myformdynamic').valid()) {
                    formSubmitById('myformdynamic', 'myformdynamic', config.customResultUrl);
                }
            });

            $(".deletedocument").click(function() { 
                var documentId = $(this).attr('id');
                $.ajax({
                    url: config.deleteDocumentUrl,
                    type: 'post',
                    dataType: 'json',
                    data: {
                        id: documentId
                    },
                    showLoader: true,
                    complete: function(result) {
                        location.reload(true);
                        $('html, body').animate({
                        scrollTop: $(".columns").offset().top
                        }, 0);
                    },
                    error: function(xhr, status, errorThrown) {}
                });
            });

            $('button#updatebtnsave').click( function(e) {
                if($('#updatedocument').valid()) {
                    formSubmitById('updatedocument', 'updatedocument', config.updateDocumentUrl);
                }
            });

            $('.update-toggle').change(function() {
                var toggleId = $(this).attr('id');
                this.value = this.checked ? 1 : 0;
                if ($(this).val() == "1") {
                    $(".expiry_dates-" + toggleId).show();
                } else {
                    $(".expiry_dates-" + toggleId).hide();
                    $("#expiry_date-" + toggleId).val('');
                }
                fnDatePicker(toggleId);
            });

            $("[id^='updatefile_']").on('change', function(e) {
                var fileId = $(this).prev().val();
                // fileRender(fileId, 'updatefile_', $(this),e);
                var val=$(this).val();
                var fileId = $(this).prev().val();
                if (val) {
                    var extension = val.substring(val.lastIndexOf('.') + 1).toLowerCase();
                    var extensionArr = ['pdf', 'jpg', 'png'];
                    if (extensionArr.indexOf(extension) >= 0) {
                        
                        $("#doc-actions-filename_" + fileId).remove();
                        var reader = new FileReader();
                        reader.onload = function(event) {
                            if (event.target.result) {
                                var filename = jQuery('#updatefile_' + fileId).val();
                                var extension = filename.replace(/^.*\./, '');
                                var nameArr = filename.split('\\');
                                if (extension == 'pdf') {
                                    var filePreview = config.pdfImg;
                                    var filesrc = URL.createObjectURL(e.target.files[0])
                                    var imgClass = "pdf-image";
                                } else {
                                    var filePreview = URL.createObjectURL(e.target.files[0]);
                                    var filesrc = URL.createObjectURL(e.target.files[0]);
                                    var imgClass = "";
                                }
                                var updatename = nameArr[2];

                                $('<div class="comman-doc-name document'+ fileId + '">' +
                                '<span>'+updatename+'</span>'+
                                '<a class="comman-download-doc" id="downloaddocument-filename_'+fileId+'" href="' + filesrc + '" target="_blank" download></a>' +
                                '</div>').insertAfter('.update-doc-'+ fileId);

                                $('<img class="previewimage-filename_' + fileId + ' ' + imgClass + '" height="170" width="170" src="' + filePreview + '" title="' + filename + '"/>' +
                                    '<div class="doc-actions" id="doc-actions-filename_' + fileId + '">' +
                                    '<a class="view-doc-link" id="view-doc-link-filename_' + fileId + '" href="' + filesrc + '" target="_blank"></a>' +
                                    '<a class="deletedocument-preview deletedocument" id="deletedocument-filename_' + fileId + '"></a>' +
                                    '</div>').insertAfter("#updatefile_" + fileId);
                                /*remove error & required message*/
                                $("#image-error-message-" + fileId).html("");
                                $("#updatefile_" + fileId +"-error").remove();
                                /*remove error message*/

                                 $("#deletedocument-filename_" + fileId).click(function() {

                                        $("#doc-actions-filename_" + fileId).remove();
                                        $('.document'+ fileId).remove();
                                        $('.previewimage-filename_' + fileId).remove();
                                        /*remove file after delete*/
                                        $("#updatefile_"+ fileId).val('');
                                        /*remove file after delete*/
                                        $(".update-doc-" + fileId).closest('.upload').removeClass('active');
                                    });
                            }
                        }
                        reader.readAsDataURL(e.target.files[0]);
                        $(document).on('change', "#toggle-" + fileId, function() {
                            this.value = this.checked ? 1 : 0;
                            if ($(this).val() == "1") {
                                $(".expiry_date-" + fileId).show();
                                fnDatePicker(fileId);
                            } else {
                                $(".expiry_date-" + fileId).hide();
                            }
                        });
                        expireOnChange(fileId);
                        $(this).closest('.upload').addClass('active');
                        $("#btnsave, #updatebtnsave").show();

                        
                    } else {
                        $(this).val('');
                        $(this).closest('.upload').removeClass('active');
                        $("#image-error-message-" + fileId).html("File type supported are JPG,PNG and PDF Only");
                    }
                } else {
                    $(this).closest('.upload').removeClass('active');
                    $("#btnsave, #updatebtnsave").hide();
                }
            });
        });
    };
});