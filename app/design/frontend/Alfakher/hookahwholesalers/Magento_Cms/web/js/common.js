require(['jquery', 'owlcarousel'], function($) {
    $(document).ready(function() {
		// $(".hookahshop").click(function(){
		// 	var url = $(this).attr('data-shopnow'); 
		// 	window.open(url, '_self');
		// });
        $('.owl-carousel').owlCarousel({
	        dots:false,
	        loop: true,
	        margin: 10,
	        nav: true,
	        navText: [
		        "&#8666",
		        "&#10140"
	        ],
	        autoplay: true,
	        autoplayHoverPause: true,
	        responsive: {
	            0: {
	              items: 1
	            },
	            600: {
	              items: 3
	            },
	            1000: {
	              items: 5
	            }
	        }
	    });
    });
});