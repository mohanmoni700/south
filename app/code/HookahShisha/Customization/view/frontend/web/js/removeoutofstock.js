define([
    "jquery",
    "jquery/ui"
    ], function($){
        "use strict";
        function main(config) {

            var AjaxUrl = config.AjaxUrl;
            $(document).on('click','.remove_outof_stock',function() {
                $.ajax({
                    showLoader: true,
                    url: AjaxUrl,
                    type: "POST",
                    success: function (data) {
                        if(data.message == 'success') {
                            $('#alfakher-alert').append(data.value);
                            $('#alfakher-alert').show();
                            window.location.reload();
                        }
                    }
                });
            });
        };
    return main;
});