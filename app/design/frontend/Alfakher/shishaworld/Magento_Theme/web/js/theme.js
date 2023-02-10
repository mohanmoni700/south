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
        $("body").on('click','#showlgpass',function(){
            if($(this).hasClass('fa-eye-slash')) {
              $(this).removeClass('fa-eye-slash');
              $(this).addClass('fa-eye');
              $(this).parent('.control').find('input').attr('type','text');
            } else {
              $(this).removeClass('fa-eye');
              $(this).addClass('fa-eye-slash');  
              $(this).parent('.control').find('input').attr('type','password');
            }
        });

    });

    $(".login.primary").click(function() {
        $('html,body').animate({
            scrollTop: $(".login-container").offset().top},
            'slow');
    });
    $('.block-collapsible-nav .block-collapsible-nav-title strong').html($('.block-collapsible-nav .block-collapsible-nav-content strong').html());

    $(".clp_faq_toggle").click(function(){
        $(".clp_faq_toggle").toggleClass('active');
        $(".clp_faq_inner").toggle();
    });
    
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

    function lazyLoadImages() {
        var e = document.querySelectorAll("img[image-data-src]");
        [].forEach.call(e, function(e) {
            isElementInViewport(e) && (e.setAttribute("src", e.getAttribute("image-data-src")),
             e.removeAttribute("image-data-src"))
        }), 0 == e.length && (window.removeEventListener("DOMContentLoaded", lazyLoadImages), 
        window.removeEventListener("load", lazyLoadImages), window.removeEventListener("resize", lazyLoadImages),
        window.removeEventListener("scroll", lazyLoadImages))
    }
    window.addEventListener("DOMContentLoaded", lazyLoadImages), window.addEventListener("load", lazyLoadImages),
    window.addEventListener("resize", lazyLoadImages), window.addEventListener("scroll", lazyLoadImages)
    function isElementInViewport(e) {
        var t = e.getBoundingClientRect();
        return true;
    }
    function isElementInViewports(e) {
        var t = e.getBoundingClientRect();
        return t.top >= 0 && t.left >= 0 && t.bottom <= (window.innerHeight || document.documentElement.clientHeight) 
            && t.right <= (window.innerWidth || document.documentElement.clientWidth)
    }
    document.addEventListener('scroll', function () {
        window.addEventListener("DOMContentLoaded", lazyLoadImages), window.addEventListener("load", lazyLoadImages),
        window.addEventListener("resize", lazyLoadImages), window.addEventListener("scroll", lazyLoadImages)    
    });

    jQuery(".sign-in").click(function(){
        jQuery("#switcher-language .switcher-options, #switcher-language .switcher-options .switcher-trigger").removeClass("active");
        jQuery(".amquote-cart-wrapper.minicart-wrapper").removeClass("-active");
        jQuery(".amquote-cart-wrapper.minicart-wrapper .mage-dropdown-dialog").css("display", "none");
      });
    jQuery(".amquote-cart-wrapper .amquote-showcart").click(function(){
        jQuery("#switcher-language .switcher-options, #switcher-language .switcher-options .switcher-trigger").removeClass("active");
        jQuery(".sign-in .customer_logged_in").css("display", "none");
    });
    jQuery(".minicart-wrapper .showcart").click(function(){
        jQuery("#switcher-language .switcher-options, #switcher-language .switcher-options .switcher-trigger").removeClass("active");
        jQuery(".sign-in .customer_logged_in").css("display", "none");
    });
    jQuery("#switcher-language #switcher-language-trigger").click(function(){
        jQuery(".sign-in .customer_logged_in").css("display", "none");
        jQuery(".minicart-wrapper .mage-dropdown-dialog").css("display", "none");
        jQuery(".amquote-cart-wrapper.minicart-wrapper").removeClass("-active");
        jQuery(".amquote-cart-wrapper.minicart-wrapper .mage-dropdown-dialog").css("display", "none");
    });
});
