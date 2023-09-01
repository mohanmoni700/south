require(['jquery'],function($){
    $(document).ready(function() {
    let tabData = [];
    $('.product-full-width-section').each(function() {
        let $section = $(this);
        let $h2 = $section.find('.product-section-title');
        let $content = $section.find('p');

        if ($h2.length > 0 && $content.length > 0) {
            tabData.push({
                title: $h2.text(),
                content: $content.html()
            });
        }
    });
    let $containertab = $('<div id ="container1"></div>');
    $('.pagebuilder-attributes-wrapper').prepend($containertab);
    let $navTabs = $('<ul class="nav-tabs"></ul>');
    tabData.forEach(function(tab, index) {
        let $navTab = $('<h3 class="nav-tab"></h3>')
            .text(tab.title)
            .attr('data-tab', 'tab' + index);
        $navTabs.append($navTab);
    });
    let $Sectiontabs = $('<div class="section-tabs"></div>');

    tabData.forEach(function(tab,index){
        let $sectionTab = $('<div class="section-tab"></div>')
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
        let targetTab = $(this).data('tab');
        $('.nav-tab').removeClass('active');
        $(this).addClass('active');
        $('.section-tab').addClass('d-none');
        $('.section-tab[data-tab="' + targetTab + '"]').removeClass('d-none');
    });
    });
});
