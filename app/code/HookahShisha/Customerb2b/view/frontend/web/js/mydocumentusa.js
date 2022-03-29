define([
	'jquery',
	'Magento_Ui/js/modal/modal',
	'mage/calendar',
	'mage/validation',
], function($,modal) {
	'use strict';

	return function(config) { //NOSONAR

	    /**
	     * Expiry Date Picker
	     *
	     * @param {Number} showId
	     */
	    function fnDatePicker(showId) {
	        $("#expiry_date" + showId).datepicker({
	            minDate: 1,
	            showMonthAfterYear: false,
	            dateFormat: 'mm/dd/yy',
	            changeMonth: true,
	            changeYear: true,
	            yearRange: '2020:2030',
	        });
	    }

	    /**
	     * Toggle change by ID
	     *
	     * @param {Number} id
	     */
	    function toggleChangeById(id) {
	        $('#toggle' + id).on('change', function() {
	            this.value = this.checked ? 1 : 0;
	            if ($(this).val() == "1") {
	                $(".expiry_dates" + id).show();
	            } else {
	                $(".expiry_dates" + id).hide();
	            }
	            fnDatePicker(id);
	        }).change();
	    }

	    /** File Preview Start;
	     *
	     * @param {Object} e
	     * @param {String} fileId
	     * @param {String} expiry_date
	     * @param {String} usabtnsave
	     * @param {String} currentElem
	     */
	    function handleFile(e, fileId, expiry_date, usabtnsave, currentElem) {

	        var reader = new FileReader();
	        reader.onload = function(event) {
	            if (event.target.result) {
	                var filename = jQuery("#" + fileId).val();
	                var extension = filename.replace(/^.*\./, '');
	                var nameArr = filename.split('\\');
	                var filePreview = "";
	                var filesrc = "";
	                var imgClass = "";
	                if (extension == 'pdf') {
                         filePreview = config.pdfImg;
                    	 filesrc = URL.createObjectURL(e.target.files[0]);
                         imgClass = "pdf-image";
                    } else {
                         filePreview = URL.createObjectURL(e.target.files[0]);
                         filesrc = URL.createObjectURL(e.target.files[0]);
                         imgClass = "";
                    }
                    
                    $('<div class="comman-doc-name document'+fileId+'">' +
                      '<span>'+nameArr[2]+'</span>'+
                      '<a class="comman-download-doc" id="downloaddocument-filename'+fileId+'" href="' + filesrc + '" target="_blank" download></a>' +
                      '</div>').insertAfter('#input-'+fileId);

	                $(".previewimage-" + fileId).show();
	                $(".previewimage-" + fileId).attr("src", filePreview);
	                $(".previewimage-" + fileId).attr("title", filename);
	                $(".previewimage-" + fileId).addClass(imgClass);

	                currentElem.closest('.upload').addClass('active');
	                $("#view-doc-link-" + fileId).show();
	                $("#doc-actions-" + fileId).show();
	                $("#view-doc-link-" + fileId).attr("href", filesrc);
	                $("#downloaddocument-" + fileId).show();
	                $("#downloaddocument-" + fileId).attr("href", event.target.result);
	                $("#download-doc-preview-" + fileId).attr("href", event.target.result);
	                $("#download-doc-preview-" + fileId).attr("download", nameArr[2]);
	                $(expiry_date).show();
	                $(usabtnsave).show();

	                $("#deletedocument-" + fileId).click(function() {
	                	var splitid = fileId.match(/\d+/g);
	                	$("#toggle" + splitid[0]).val("0");
	                	this.value = this.checked ? 1 : 0;
						if ($(this).val() == "1") {
							$(".expiry_dates" + splitid[0]).show();
						} else {
							$("#toggle" + splitid[0]).attr('checked',false);
							$(".expiry_dates" + splitid[0]).hide();
						}
	                    $("#expiry_date" + splitid[0]).val("");
                        $("#expiry_date" + splitid[0]).attr('class',"");
	                    $("#doc-actions-" + fileId).hide();
	                    $(".previewimage-" + fileId).removeClass(imgClass);
	                    $(".previewimage-" + fileId).hide();
	                    $(".document" + fileId).hide();
	                    $("#view-doc-link-" + fileId).hide();
	                    $("#" + fileId).val('');
	                    $(this).closest('.upload').removeClass('active');
	                });
	            }
	        }
	        reader.readAsDataURL(e.target.files[0]);
	    }

	    /** File uploading by ID
	     *
	     * @param {Number} showId
	     * @param {String} fileId
	     */
	    function fileUploading(showId, fileId, currentElem, e) {
	    	var val = currentElem.val();
	    
	    	if (val) {
                /*bv-hd Dynaminc Image and PDF validation*/
                var extension = val.substring(val.lastIndexOf('.') + 1).toLowerCase();
                var extensionArr = ['pdf', 'jpg', 'png'];
                if (extensionArr.indexOf(extension) >= 0) { //NOSONAR

                    $(this).closest('.upload').find('.doc_name').remove();
                    $(this).closest('.upload').find('.set_expiry').remove();
                    var filesrc = URL.createObjectURL(e.target.files[0]);
                    var filename = jQuery('#'+ fileId + showId).val();
                    var nameArr = filename.split('\\');
                    var name = nameArr[2];
                    // added new class add-new_[BS]
                    $('<div class="comman-doc-name document'+showId+'">' +
                        '<span>'+name+'</span>'+
                        '<a class="comman-download-doc" id="downloaddocument-filename'+showId+'" href="' + filesrc + '" target="_blank" download></a>' +
                        '</div>'+
                        '<div class="doc_name add-new_'+showId+'" for="document name">' +
                        '<label>Document Name</label>' +
                        '<input type="text" id="name-' + showId + '" name="name' + showId + '" data-validate="{required:true}"/></div>' +
                        '<div class="set_expiry add-new_'+showId+'" for="document expiry data" style="display:none">' +
                        '<label>Set a Specific Expiry date</label>' +
                        '<input type="checkbox" name="set_expiry-' + showId + '" id="toggle' + showId + '" value="0" class="cmn-toggle cmn-toggle-round" />' +
                        '<span class="slider round"></span>' +
                        '<div class="expiry_dates expiry_dates' + showId + '" for="document expiry data" style="display:none">' +
                        '<label>Set Expiry Date</label>' +
                        '<input type="text" placeholder="Expiry Date" name="expiry_date'+ showId +'" id="expiry_date' + showId + '" data-validate="{required:true}" readonly/>' +
                        '<input type="hidden" id="is_add_more_form'+ showId +'" name="is_add_more_form'+ showId +'" value="1"/>'+
                        '</div></div>').insertAfter("#input-file-" + showId);

                    $("#image-error-message" + showId).html("");
                    currentElem.parent().find('.input-note').hide();
                    currentElem.closest('.upload').addClass('active');
                } else {
                    currentElem.val('');
                	currentElem.closest('.upload').removeClass('active');
                    $("#image-error-message" + showId).html("File type supported are JPG,PNG and PDF Only");
                }

                $("#doc-actions-filename-" + showId).remove();

                var reader = new FileReader();
                reader.onload = function(event) {
                    if (event.target.result) { //NOSONAR
                    	var filesource = URL.createObjectURL(e.target.files[0]);
                    	var fileView = "";
                    	var imgClass = "";
                        if (extension == 'pdf') {
	                         fileView = config.pdfImg;
	                         imgClass = "pdf-image";
	                    } else {
	                         fileView = URL.createObjectURL(e.target.files[0]);
	                         imgClass = "";
	                    }

                        $('<img class="previewimage-filename-' + showId + ' ' + imgClass + '" height="170" width="170" src="' + fileView + '" title="' + filename + '"/>' +
                            '<div class="doc-actions" id="doc-actions-filename-' + showId + '">' +
                            '<a class="view-doc-link" id="view-doc-link-filename-' + showId + '" href="' + filesource + '" target="_blank"></a>' +
                            '<a class="deletedocument-preview deletedocument" id="deletedocument-filename' + showId + '"></a>' +
                            '</div>').insertAfter("#" + fileId + showId);

                        $("#deletedocument-filename" + showId).click(function() {
                            $("#doc-actions-filename-" + showId).remove();
                            //remove multi div append[BS]
                            $(".add-new_"+showId).remove();
                            $('#'+ fileId + showId).val('');
                            $(".previewimage-filename-" + showId).hide();
		                    $(".document" + showId).hide();
		                    $("#view-doc-link-" + showId).hide();
		                    $("#updatefile_" + showId + ", #filename-"+showId).closest('.upload').find('.input-note').show();
                            $("#updatefile_" + showId + ", #filename-"+showId).closest('.upload').removeClass('active');
                        });
                    }
                }
                reader.readAsDataURL(e.target.files[0]);

                toggleChangeById(showId);

                $(document).on('change', "#expiry_date" + showId, function() {
                    if ($(this).val()) {
                        var date_val = $(this).val().indexOf('Expiry Date: ') == -1 ? $(this).val() : $(this).val().split('Expiry Date: ')[1];
                        $(this).val('Expiry Date: ' + date_val);
                        $(this).addClass('active-date');
                    }
                });
                $(this).closest('.upload').addClass('active');
            } else {
                $(this).closest('.upload').removeClass('active');
            }
	    }

	    /** File change action by ID
	     *
	     * @param {Number} id
	     */
	    function fileChangeById(id) {
	        $(".doc_name" + id).hide();
	        $(".set_expiry" + id).hide();

	        $('#filename' + id).change(function(e) {
	            var val = $(this).val();
	            if (val) {
	                var extension = val.substring(val.lastIndexOf('.') + 1).toLowerCase();
	                var extensionArr = ['pdf', 'jpg', 'png'];
	                if (extensionArr.indexOf(extension) >= 0) {
	                    $("#image-error-message" + id).html("");
		                handleFile(e, "filename" + id, ".set_expiry" + id, "#usabtnsave", $(this));
	        			$(".doc_name" + id).show();
		                $(".set_expiry" + id).show();
		                $("#usabtnsave").show();
	                } else {
	                    $(this).val('');
	                    $("#image-error-message" + id).html("File type supported are JPG,PNG and PDF Only");
	                }
	            } else {
	        		$(".doc_name" + id).hide();
	                $(".set_expiry" + id).hide();
	                $("#usabtnsave").hide();
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
	        $("form#"+id).submit(function(e) {
	            e.preventDefault();
	            var formData = new FormData(this);
	            $.ajax({
	                url: postUrl,
	                type: 'POST',
	                data: formData,
	                dataType: 'json',
	                showLoader: true,
	                cache: false,
	                contentType: false,
	                processData: false,
	                success: function(data) {
	                    if (data.success == 1) {
	                        $('#' + resetId).trigger("reset");
	                        $("#popup-modal-non-usa").show();
                            $("#usaform").hide();
                            $('.page-title-wrapper').hide();
                            $('.form-create-account').hide();
                            $("#usa_customer_tab .document-title").hide();
	                    } else {
	                        $(".page.messages").html('<div role="alert" class="messages">' +
	                            '<div class="alert danger alert-danger" data-ui-id="message-danger">' +
	                            '<div>Data could not saved !.</div>' +
	                            '</div></div>');
	                        $(".page.messages").delay(200).fadeIn().delay(4000).fadeOut();
	                    }
	                },
	            });
	        });
	    }

	    $(document).ready(function() {

	        toggleChangeById(1);
	        toggleChangeById(2);
	        toggleChangeById(3);
	        toggleChangeById(4);

	        fileChangeById(1);
	        fileChangeById(2);
	        fileChangeById(3);
	        fileChangeById(4);

	        $("#add_more").click(function() {
	        	var id = jQuery('#usaform').find('input[type="file"]').length;
	        	var showId = ++id;
	            if (showId <= 25) {
	                $('<input type="hidden" name="is_customerfrom_usa' + showId + '" value="1"/>'+
                	'<div class="upload" for="document uploader"><div class="input-file" id="input-file-' + showId + '">' +
                    '<input type="file" id="filename-' + showId + '" class="upload-filename-' + showId +'" name="filename' + showId + '" data-validate="{required:true}" />' +
                    '<span class="input-note">' + config.inputNote + '</span>'+
                    '<span id="image-error-message'+showId+'" style="color:red;"></span>'+
                    '</div></div>').insertBefore('.upload-container .usa-add-more');
	            }
	            if (showId == 25){
                    $(".add-more-cont").hide();
                    $("#add_more").hide();
                }
	            $('#filename-' + showId).change(function(e) {
	                fileUploading(showId, 'filename-', $(this), e);
	            });
	        });


	        /**  Making hide/show add more container based on checkbox [AR] */
	        $('.add-more-field .checkbox').on('change', function() {
	            this.value = this.checked ? 1 : 0;
	            if ($(this).val() == "1") {
	                $(".add-more-container").show();
	                $(".add-more-field .note").show();
	                if($('.upload-container.add-more-container .upload').length == 0)
	                {
	                	$('#add_more').click();
	                }
	            } else {
	                $(".add-more-container").hide();
	                $(".add-more-field .note").hide();
	            }
	        }).change();

	        $('#filename1,#filename2,#filename3,#filename4').change(function() {
	            if ($(this).val()) {
	                $(this).closest('.upload').addClass('active');
	                $("#usabtnsave").show();
	                $(this).parent().find('img').show();
	            } else {
	                $(this).closest('.upload').removeClass('active');
	                $("#usabtnsave").hide();
	                $(this).parent().find('img').hide();
	            }
	        });

	        $('#expiry_date1, #expiry_date2, #expiry_date3, #expiry_date4').change(function() {
	            if ($(this).val()) {
	                var date_val = $(this).val();
	                $(this).val('Expiry Date: ' + date_val);
	                $(this).addClass('active-date');
	            }
	        });

	        $('button#usabtnsave').click( function(e) {
                $(".pending-tooltip").hide();  
		        if($('#usaform').valid()) {
		        	formSubmitById('usaform', 'usaform', config.customResultUrl);
		        }
		    });
	    });
	};
});