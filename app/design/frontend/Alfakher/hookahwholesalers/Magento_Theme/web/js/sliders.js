define([
    'jquery',
    'Magento_PageBuilder/js/resource/slick/slick'
], function ($) {
    'use strict';

     return function (config) {
         // Upsell Slider
         $('.products-upsell .product-items').slick({
            dots: false,
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: false,
            infinite: false,
            responsive: [{
                breakpoint: 1224,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 1
                    }
                },
                {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1
                    }
                },
                {
                breakpoint: 425,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                }
            }]
        });
    }

});




