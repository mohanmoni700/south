/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 require(['jquery'],function($){
	
    $(document).ready(function() {
        
         // sidebar filter toggle
         jQuery( document ).on('click','.filter-options-title',function() {
            jQuery( this ).toggleClass( 'rotate' ); 
            jQuery( this ).next().toggle();
        });
       
    });
});
