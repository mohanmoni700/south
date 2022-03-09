require(['jquery'], function($) {
	 $(document).ready(function() {
	     $(".hookahshop").click(function(){
		     var url = $(this).attr('data-shopnow'); 
             window.open(url, '_self');
		 });
    });
});	 