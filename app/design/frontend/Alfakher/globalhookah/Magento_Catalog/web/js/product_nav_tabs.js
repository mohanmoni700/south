require(['jquery'],function($){
    $(document).ready(function() {
    var tabData = [];
    $('.product-full-width-section').each(function() {
        var $section = $(this);
        var $h2 = $section.find('.product-section-title');
        var $content = $section.find('p');

        if ($h2.length > 0 && $content.length > 0) {
            tabData.push({
                title: $h2.text(),
                content: $content.html()
            });
        }
    });
    var $containertab = $('<div id ="container1"></div>');
    $('.pagebuilder-attributes-wrapper').prepend($containertab);
    var $navTabs = $('<ul class="nav-tabs"></ul>');
    tabData.forEach(function(tab, index) {
        var $navTab = $('<h3 class="nav-tab"></h3>')
            .text(tab.title)
            .attr('data-tab', 'tab' + index);
        $navTabs.append($navTab);
    });
    var $Sectiontabs = $('<div class="section-tabs"></div>');

    tabData.forEach(function(tab,index){
        var $sectionTab = $('<div class="section-tab"></div>')
        .text(tab.content)
        .attr('data-tab','tab'+index);
        $Sectiontabs.append($sectionTab)

    });
    $('#container1').append($navTabs);
    $('#container1').append($Sectiontabs);
    $('.nav-tab:first').addClass('active');
    $('.section-tab').addClass('d-none');
    $('.section-tab:first').removeClass('d-none');
    $('.nav-tab').on('click', function() {
        var targetTab = $(this).data('tab');
        $('.nav-tab').removeClass('active');
        $(this).addClass('active');
        $('.section-tab').addClass('d-none');
        $('.section-tab[data-tab="' + targetTab + '"]').removeClass('d-none');
    });
    });
});
