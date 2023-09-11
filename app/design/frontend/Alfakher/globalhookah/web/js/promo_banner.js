/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require(['jquery', 'mage/cookies'],function($){
    function handleClose() {
        $.cookie('legal-banner', 'closed', {
            path: '/',
            expires: new Date(Date.now() + 86400 * 1000)
        });
        
        let promoBanner = $('.warning-section-wrapper');
        promoBanner.css("display",'none');
    }

    $(document).ready(function() {
        const closButton = $('.promo-btn');
        let isClosed = $.cookie('legal-banner');
        
        if (isClosed) {
            let promoBanner = $('.warning-section-wrapper');
            promoBanner.css("display",'none');
        }
        closButton.on('click', handleClose); 
    });
});