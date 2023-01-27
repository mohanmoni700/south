require(['jquery', 'Magento_Ui/js/modal/alert', 'mage/translate', 'domReady!'], function ($, alert, $t) {

    window.slopeKeyValidator = function (endpoint, environmentId) {
        environmentId = $('[data-ui-id="' + environmentId + '"]').val();

        let publicKey = '', privateKey = '';

        if (environmentId === 'sandbox') {
            publicKey = $('[data-ui-id="password-groups-slope-payment-groups-slopeapi-fields-publickey-sandbox-value"]').val();
            privateKey = $('[data-ui-id="password-groups-slope-payment-groups-slopeapi-fields-privatekey-sandbox-value"]').val();
        } else {
            publicKey = $('[data-ui-id="password-groups-slope-payment-groups-slopeapi-fields-publickey-production-value"]').val();
            privateKey = $('[data-ui-id="password-groups-slope-payment-groups-slopeapi-fields-privatekey-production-value"]').val();
        }

        /* Remove previous success message if present */
        if ($(".slopepayment-credentials-success-message")) {
            $(".slopepayment-credentials-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!environmentId || environmentId !== 'sandbox' && environmentId !== 'production') {
            errors.push($t("Please select an Environment"));
        }

        if (!publicKey) {
            errors.push($t('Please enter a Public Key'));
        }

        if (!privateKey) {
            errors.push($t('Please enter a Private Key'));
        }

        if (errors.length > 0) {
            alert({
                title: $t('Slope Credential Validation Failed'),
                content:  errors.join('<br>')
            });
            return false;
        }

        $(this).text($t("We're validating your credentials...")).attr('disabled', true);

        var self = this;
        $.ajax({
            type: 'POST',
            url: endpoint,
            data: {
                environment: environmentId,
                public_key: publicKey,
                private_key: privateKey
            },
            showLoader: true,
            success: function (result) {
                if (result.success === 'true') {
                    $('<div class="message message-success slopepayment-credentials-success-message">' + $t("Your credentials are valid.") + '</div>').insertAfter(self);
                } else {
                    alert({
                        title: $t('Slope Credential Validation Failed'),
                        content: $t('Your Slope API Credentials could not be validated.<br>Please ensure you have selected the correct environment and entered a valid Public Key and Private Key')
                    });
                }
            }
        }).always(function () {
            $(self).text($t("Validate Credentials")).attr('disabled', false);
        });
    };
});
