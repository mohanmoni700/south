define(
    [
        'jquery',
        'underscore',
        'mage/template'
    ],
    function ($, _, mageTemplate) {
        'use strict';

        return {
          
            build: function (formData) {
                var formTmpl;
                var strUrl = '';
                
                if(formData.fields['integration_type'] == 'iframe'){
                    $.each( formData.fields, function( key, val ) {
                        strUrl += '&'+key+'='+val;
                    });

                    formTmpl = mageTemplate('<div id="iframe-warning" class="message notice">'+
                            '<div>Please do not refresh the page until you complete payment.</div>'+
                        '</div><iframe id="ccavenue_payment_iframe" src="<%= data.action %>" class="checkout-iframe" scrolling="no" frameborder="0" border="0" height="610" width="100%">'+
                    '</iframe>');
                }else{
                    formTmpl = mageTemplate('<form action="<%= data.action %>" id="ccavenue_payment_form"' +
                        ' method="POST" hidden enctype="application/x-www-form-urlencoded">' +
                            '<% _.each(data.fields, function(val, key){ %>' +
                                '<input value=\'<%= val %>\' name="<%= key %>" type="hidden">' +
                            '<% }); %>' +
                        '</form>');
                }

                return $(formTmpl({
                    data: {
                        action: formData.action + strUrl,
                        fields: formData.fields,
                    }
                })).appendTo($('#iframe_wrapper'));
            }

        };
    }
);
