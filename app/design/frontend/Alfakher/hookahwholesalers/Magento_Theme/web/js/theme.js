define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    $(window).on('load',function() {
        $('img').each(function(){
            var extensionType = $(this).attr('src').replace(/^.*\./, '');
            if(($(this).attr('loading') == undefined) &&
                extensionType != "gif") {
                $(this).attr('loading','lazy');
            }
        });
    });

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');
    $('#store\\.links li a').each(function () {
        var id = $(this).attr('id');

        if (id !== undefined) {
            $(this).attr('id', id + '_mobile');
        }
    });

    keyboardHandler.apply();

    $('.blog-page-list .block.block-products-list.grid').insertAfter('.blog-page-list .page-main > .columns');

    $(".clp_faq_toggle").click(function(){
        $(".clp_faq_toggle").toggleClass('active');
        $(".clp_faq_inner").toggle();
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
        return t.top >= 0 && t.left >= 0 && t.bottom <= (window.innerHeight 
        || document.documentElement.clientHeight) 
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
