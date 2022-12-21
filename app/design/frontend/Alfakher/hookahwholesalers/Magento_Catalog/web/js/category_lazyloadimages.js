require(['jquery', 'jquery/ui'],function(){
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
});
