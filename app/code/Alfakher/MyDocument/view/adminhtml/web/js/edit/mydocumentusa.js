define([
	'jquery',
	'Magento_Ui/js/modal/modal',
	'mage/calendar',
	'mage/validation',
], function($,modal) {
	'use strict';

	return function(config) {
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
                if (extensionArr.indexOf(extension) >= 0) {
                    $(this).closest('.upload').find('.doc_name').remove();
                    $(this).closest('.upload').find('.set_expiry').remove();
                    // added new class add-new_[BS]
                    var filesrc = URL.createObjectURL(e.target.files[0]);
                    var filename = jQuery('#'+ fileId + showId).val();
                    var nameArr = filename.split('\\');
                    var name = nameArr[2];

                    $('<div class="comman-doc-name document'+showId+'">' +
                        '<span>'+name+'</span>'+
                        '<a class="comman-download-doc" id="downloaddocument-filename'+showId+'" href="' + filesrc + '" target="_blank" download></a>' +
                        '</div>').insertAfter("#input-updatefile-" + showId);

                    $('<div class="comman-doc-name document'+showId+'">' +
                        '<span>'+name+'</span>'+
                        '<a class="comman-download-doc" id="downloaddocument-filename'+showId+'" href="' + filesrc + '" target="_blank" download></a>' +
                        '</div>').insertAfter("#input-file-" + showId);

                    $("#image-error-message" + showId).html("");
                    currentElem.parent().find('.input-note').hide();
                    currentElem.closest('.upload').addClass('active');
                } else {
                    currentElem.val('');
                	currentElem.closest('.upload').removeClass('active');
                    $("#image-error-message" + showId).html("File type supported are JPG,PNG and PDF Only");
                    return false;
                }

                $("#doc-actions-filename-" + showId).remove();

                var reader = new FileReader();
                reader.onload = function(event) {
                    if (event.target.result) {
                        var filename = jQuery('#'+ fileId + showId).val();
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

                        $('<img class="previewimage-filename-' + showId + ' ' + imgClass + '" height="170" width="170" src="' + filePreview + '" title="' + filename + '"/>' +
                            '<div class="doc-actions" id="doc-actions-filename-' + showId + '">' +
                            '<a class="view-doc-link" id="view-doc-link-filename-' + showId + '" href="' + filesrc + '" target="_blank">view</a>' +
                            '<a class="deletedocument-preview deletedocument" id="deletedocument-filename' + showId + '">del</a>' +
                            '</div>').insertAfter("#" + fileId + showId);

                        $(document).on('click', "#deletedocument-filename" + showId, function(e) {
                        //$("#deletedocument-filename" + showId).click(function() {
                            $("#doc-actions-filename-" + showId).remove();
                            //remove multi div append[BS]
                            $(".add-new_"+showId).remove();
                            $('#'+ fileId + showId).val('');
                            $(".previewimage-filename-" + showId).hide();
                            
                            //remove pdf class[BS]
	                    	$(".previewimage-filename-" + showId).removeClass(imgClass);
		                    $("#view-doc-link-" + showId).hide();
		                    $(".document" + showId).hide();
		                    $("#updatefile_" + showId + ", #filename-"+showId).closest('.upload').find('.input-note').show();
                            $("#updatefile_" + showId + ", #filename-"+showId).closest('.upload').removeClass('active');
                        });
                    }
                }
                reader.readAsDataURL(e.target.files[0]);
                $(this).closest('.upload').addClass('active');
            } else {
                $(this).closest('.upload').removeClass('active');
            }
	    }

	    $(document).ready(function() {
	    	$(document).on('click', ".deleteuploadeddoc", function(e) {		    
	        //$(".document .deletedocument").click(function() {
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
	                    $('html, body').animate({scrollTop:70});
	                },
	                error: function(xhr, status, errorThrown) {}
	            });
	        });

	    	$(document).on('change', "[id^='updatefile_']", function(e) {
	        //$("[id^='updatefile_']").on('change', function(e) {
	            var showId = $(this).prev().val();
	            fileUploading(showId, 'updatefile_', $(this), e);
	        });
	    });
	};
});