define([
    'jquery'
], function($) {
    'use strict';
        var form = $('form.subscribe');

        form.submit(function(e) {
            if(form.validation('isValid')){
                var email = $("#newsletter").val();
                var url = form.attr('action');
                var loadingMessage = $('#loading-message');

                if(loadingMessage.length == 0) {
                    $('<div id="loading-message" style="display:none;padding-bottom:15px">&nbsp;</div>').insertBefore("#newslettermsg");
                    var loadingMessage = $('#loading-message');
                }
                e.preventDefault();
                try{
                    loadingMessage.css("color","green");
                    loadingMessage.html('Submitting...').show();
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        type: 'POST',
                        data: {email: email},
                        success: function (data){
                            if(data.status != "ERROR"){
                                $('#newsletter').val('');
                                loadingMessage.css("color","green");
                            }else{
                                loadingMessage.css("color","red");
                            }
                            loadingMessage.html(data.msg);
                        },
                        complete: function(){
                            setTimeout(function(){
                                loadingMessage.hide();
                            },10000);
                            $(".subscribe-enable").attr("disabled",false);
                        }
                    });
                } catch (e){
                    loadingMessage.html(e.message);
                }
            }
            return false;
        });
   });