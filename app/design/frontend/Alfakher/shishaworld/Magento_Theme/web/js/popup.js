define([ 'jquery',
'Magento_Ui/js/modal/modal',
'mage/cookies'], function($,modal) {

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

	var popup = modal(options, $('#popup'));
	if ($.cookie('popuplogintext') != 'open') {
		$("#popup").modal('openModal');
		$.cookie('popuplogintext', 'open', { path: '/' });//Set the cookies
	}
}
);
