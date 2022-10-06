define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');
    $('#store\\.links li a').each(function () {
        var id = $(this).attr('id');

        if (id !== undefined) {
            $(this).attr('id', id + '_mobile');
        }
    });

    keyboardHandler.apply();


    $(document).ready(function(){
        $(window).scroll(function() {
            if ($(this).scrollTop() >= 50) {        
                $('#top-return').fadeIn(200);    
            } else {
                $('#top-return').fadeOut(200);   
            }
        });
        $('#top-return').click(function() {      
            $('body,html').animate({
                scrollTop : 0                       
            }, 500);
        });

        $('.level0.parent > .level-top').click(function() {
            return false;
        });
        $(".ui-menu-item.parent.level1:first-child").addClass('current');

        //passvisible 
        jQuery(document).on('click','#showlgpass',function(){
            var vis = jQuery(this).data('vis');
            if(vis == 0){
                jQuery(this).closest('.control').find('input').attr('type','text');
                jQuery(this).removeClass('fa-eye-slash').addClass('fa-eye');
                jQuery(this).data('vis',1);
            }else{
                jQuery(this).closest('.control').find('input').attr('type','password');
                jQuery(this).removeClass('fa-eye').addClass('fa-eye-slash');
                jQuery(this).data('vis',0);
            }
        });

    });


    $(".login.primary").click(function() {
        $('html,body').animate({
            scrollTop: $(".login-container").offset().top},
            'slow');
    });
    $('.block-collapsible-nav .block-collapsible-nav-title strong').html($('.block-collapsible-nav .block-collapsible-nav-content strong').html());

    $(window).load(function() {
        $(".mobile_menu_icon").click(function(){  
            $(".mobile_menu_icon").toggleClass("change");
            $("body, html").toggleClass("active-menu");
            $('body,html').animate({
                scrollTop : 0
            }, 0);
        });

        // mobile menu custom toggle
        jQuery('.hamburger_icon').on('click', function () {
            jQuery('.sw-megamenu').toggleClass('active');
        });
    });
    $(window).on("load resize",function(e){
        var width = $(this).width();
        if(width < 1025) {
            $(".ui-menu-item.parent.level1:first-child").removeClass('current');
            jQuery('.ui-menu-item.parent.level1 > a span').click(function() {
                jQuery(this).closest('li').toggleClass('current');
                // return false;
            });
        }
        if(width > 1025) {
            $(".ui-menu-item.parent.level1:first-child").addClass('current');
            $(".ui-menu-item.parent.level1").hover(function(){
                $(this).closest('ul').find('li').not($(this)).removeClass('current');
                $(this).addClass('current');
                
                }, function(){
                $(this).closest('ul').find('li').not($(this)).removeClass('current');
            });
        }
    });       
});
