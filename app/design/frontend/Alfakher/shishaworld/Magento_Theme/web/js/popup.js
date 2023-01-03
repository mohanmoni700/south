define([ 
	'jquery',
	'Magento_Ui/js/modal/modal',
	'mage/cookies',
	'mage/mage'
], function($,modal) {
	'use strict';
	var form = $('form.subscribe');
	var options = {
		type: 'popup',
		responsive: true,
		innerScroll: true,
		title: ' ',
		modalClass: 'custom-window-block',
		buttons: [{
			class: '',
			click: function () {
			this.closeModal();
			$('#popup').html(" ");
			}
		}],
		clickableOverlay: false,
		opened: function($Event) {
		$(".modal-footer").hide();
		},
		closed: function (){
			$.cookie('popuplogintext', 'open', { path: '/' });
		}
	};
	function showSubscriptionPopup() {
		$('#popup').modal({
			closed: function (){
				var popup = modal(options, $('#popup'));
				if ($.cookie('popuplogintext') != 'open') {
					$("#popup").modal('openModal');
				}
			}
		});
	}
	$(document).keydown(function(e){
			if (e.keyCode == 27) {
				showSubscriptionPopup();
			}
			if(e.keyCode ==13){
				showSubscriptionPopup();
			}
	});
	form.submit(function(e) {
            if(form.validation('isValid')){
                var email = $("#newsletter").val();
                var url = form.attr('action');
                var loadingMessage = $('#loading-message');

                if(loadingMessage.length == 0) {
                    $('<div id="loading-message" style="display:none;padding-bottom:15px">&nbsp;</div>').insertBefore("#newslettermsg");
                    var loadingMessage = $('#loading-message');
                }
                e.preventDefault();
                try{
                    loadingMessage.css("color","green");
                    loadingMessage.html('Submitting...').show();
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        type: 'POST',
                        data: {email: email},
                        success: function (data){
                            if(data.status != "ERROR"){
                                $('#newsletter').val('');
                                loadingMessage.css("color","green");
                                $.cookie('popuplogintext', 'open', { path: '/' });
                                setTimeout(function(){
	                                $("#popup").modal('closeModal');
                            },3000);
                            }else{
                                loadingMessage.css("color","red");
                            }
                            loadingMessage.html(data.msg);
                        },
                        complete: function(){
                            setTimeout(function(){
                                loadingMessage.hide();
                            },5000);
                            $(".subscribe-enable").attr("disabled",false);
                        }
                    });
                } catch (e){
                    loadingMessage.html(e.message);
                }
            }
            return false;
        });
	$(document).ready(function() {
		var popup = modal(options, $('#popup'));
		if ($.cookie('popuplogintext') != 'open') {
			$("#popup").modal('openModal');
		}
	});
});
