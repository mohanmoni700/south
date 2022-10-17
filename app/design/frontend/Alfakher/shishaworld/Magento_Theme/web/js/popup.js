define([ 'jquery',
'Magento_Ui/js/modal/modal',
'mage/cookies',
'mage/mage'
], function($,modal) {
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

		opened: function($Event) {
		$(".modal-footer").hide();
		}
	};
	$(document).ready(function() {
	var popup = modal(options, $('#popup'));
	if ($.cookie('popuplogintext') != 'open') {
		$("#popup").modal('openModal');
	if( "#newsletter-popup" ){
		$( "#newsletter-popup" ).click(function() {
            $.cookie('popuplogintext', 'open', { path: '/' });
          });
		$( ".action-close" ).click(function() {
            $.cookie('popuplogintext', 'open', { path: '/' });
          });
		} 
	  }
	});
  }
);
