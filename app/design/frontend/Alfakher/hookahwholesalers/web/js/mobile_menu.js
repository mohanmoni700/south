/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 require(['jquery'],function($){
	
    $(document).ready(function() {
        if ($(window).width() < 1025) {
            // add class for mobile view default open
            jQuery('.block-search #search_mini_form').addClass('active');
            
            // mobile menu subchild menu toggle
            jQuery('.level1.parent .subchildmenu').hide();
            $('.level1.parent.current a').on('click', function () {
                $('.level1.parent.current .subchildmenu').toggle();
            }); 
        }   
    });
});
