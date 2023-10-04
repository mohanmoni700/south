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
            // New code for mobile menu
            $('.level0.parent > a').off('click').on('click', function (e) {  
                e.preventDefault(); 
                $('.level0.parent > .level-top').removeClass('ui-state-active'); 
                 $('.submenu').removeClass('opened');                          
                $(this).addClass('ui-state-active').parent().find('.submenu').addClass('opened');
            });
            $('.tabs-content .pagebuilder-column-group .pagebuilder-column:first-child ul').prepend('<li class="first"><div class="close-submenu">Close</div><div class="parent-name"></div></li>');
            $('.tabs-navigation li a').off('click').on('click', function () {
                let $href = $(this).attr('href');                       
                $($href).find('.parent-name').html($(this).html());
            });
            $('.tabs-navigation li').off('click').on('click', function () {
                $('.tabs-content').removeClass('active');
                $(this).parent().parent().find('.tabs-content').addClass('active');
            });
            $(' .close-submenu').off('click').on('click', function () {
                $('.tabs-content').removeClass('active');
            });            
        }
        mediaCheck({
            media: '(min-width: 1025px)',

            // Switch to Desktop version
            entry: function() {
                $('.custom.navigation .tab-header:first-child').addClass('ui-tabs-active');
                $('.custom.navigation .tab-header').on('hover', function() {
                    let controls = $(this).attr('aria-controls');
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
                $('.custom.navigation .level0.parent .level-top').off("click").on("click", function(e) {
                    e.preventDefault();
                    $(this).toggleClass('active');
                });
                $('.custom.navigation .tab-header').off('click').on('click', function() {
                    $(this).parents('.ui-menu-item').find('.level0.submenu').toggleClass('opened');
                });
            }
        });
    });
});
