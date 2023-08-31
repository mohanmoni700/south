/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require([
    'jquery',
    'Magento_PageBuilder/js/resource/slick/slick',
    'domReady!'
], function ($) {
    $(window).load(function() {
        $('.homepage_category_slider > .pagebuilder-column-group').slick({
            dots: false,
            arrows: true,
            slidesToShow: 6,
            slidesToScroll: 6,
            autoplay: false,
            infinite: false,
            touchThreshold: 1000,
            responsive: [{
                breakpoint: 1200,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 5
                    }
                },
                {
                    breakpoint: 992,
                        settings: {
                            slidesToShow: 4,
                            slidesToScroll: 4,
                            infinite: false,
                        }
                    },
                {
                breakpoint: 767,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        infinite: false,
                    }
                },
                {
                breakpoint: 640,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        infinite: false,
                    }
                },
                {
                breakpoint: 375,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        infinite: false,
                    }
                }
            ]
        });
    });
});