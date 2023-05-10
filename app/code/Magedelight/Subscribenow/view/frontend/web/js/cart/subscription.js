define([
    'jquery', 'mage/translate', 'mage/calendar'
], function ($, $t) {

    var endTypeRadio = $('.md_end_type input:radio');

    $(document).on('change', '.subscription_type_radio', function () {
        var value = $(this).val();
        var subscriptionContent = $(this).parents('.md_subscription_form').find('.md_subscription_content');
        if (value == "subscription") {
            subscriptionContent.removeClass('md_subscription_content_hidden');
        } else {
            subscriptionContent.addClass('md_subscription_content_hidden');
        }
    });

    endTypeRadio.each(function() {
        if ($(this).is(":checked")) {
            $(this).siblings('.end_type_content').show();
        }
    });
    endTypeRadio.on('change', function (event) {
        if(event.originalEvent !== undefined){
            $(this).parents('.md_subscription_content').find('.end_type_content').hide();
            $(this).siblings('.end_type_content').show();
        }
    });

    $(function () {
        $('.input-date-picker').each(function () {
            var min_date = $(this).attr('data-min-date');
            var selected_date = $(this).attr('data-date');

            $(this).calendar({
                showsTime: false,
                changeMonth: false,
                changeYear: false,
                showOn: 'focus',
                hideIfNoPrevNext: true,
                showAnim: "",
                buttonImageOnly: null,
                buttonImage: null,
                showButtonPanel: false,
                showOtherMonths: false,
                showWeek: false,
                timeFormat: '',
                showTime: false,
                showHour: false,
                showMinute: false,
                buttonText: $t('Select Date'),
                dateFormat: "dd-mm-yy",
                minDate: min_date
            });
            
            $(this).calendar().datepicker("setDate", selected_date);
        });
    });
});