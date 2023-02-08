require(['jquery',
    'matchMedia'
    ],function($, mediaCheck){
    
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
        mediaCheck({
            media: '(min-width: 1025px)',

            // Switch to Desktop version
            entry: function() {
                $('.custom.navigation .tab-header:first-child').addClass('ui-tabs-active');
                $('.custom.navigation .tab-header').on('hover', function() {
                    var controls = $(this).attr('aria-controls');
                    $(this).parents('.ui-menu-item').find('.tab-header').removeClass('ui-tabs-active');
                    $(this).addClass('ui-tabs-active');
                    $(this).parents('.ui-menu-item').find('.tabs-content').addClass('open');
                    $(this).parents('.ui-menu-item').find('.ui-tabs-panel').removeClass('ui-state-active');
                    $(this).parents('.ui-menu-item').find('.ui-tabs-panel[id="'+ controls +'"]').addClass('ui-state-active');
                });
            },

            // Switch to Mobile Version
            exit: function () {
                $('.custom.navigation .level0.submenu').removeClass('opened');
                $('.custom.navigation .level0 .level-top').off("touchstart").on("touchstart", function() {
                    $(this).toggleClass('active');
                });
                $('.custom.navigation .tab-header a').off('touchstart').on('touchstart', function(e) {
                    e.preventDefault();
                    $(this).parents('.ui-menu-item').find('.level0.submenu').toggleClass('opened');
                });
            }
        });
    });
});
