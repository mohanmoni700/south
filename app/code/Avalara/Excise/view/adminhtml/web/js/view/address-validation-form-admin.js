
define(
    [
        'underscore',
        'Avalara_Excise/js/view/address-validation-form',
        'Avalara_Excise/js/model/region-model'
    ],
    function (
        _,
        addressValidationForm,
        regionModel
    ) {
        'use strict';

        return _.extend({}, addressValidationForm, {

            // Override the base function to add the additional region data that is missing in the backend
            buildOriginalAddress: function (originalAddress) {
                try {
                    // Get country data JSON from region model
                    var countryData = regionModel.regions.responseJSON;

                    if (originalAddress.region_id && countryData[originalAddress.country_id]) {
                        // A region ID was provided and the provided country ID has region data set
                        var region = countryData[originalAddress.country_id][originalAddress.region_id];
                        if (region) {
                            // Found a matching region
                            originalAddress.region = region['name'];
                        }
                    }
                } catch (error) {
                    // Don't need to do anything here
                }

                // Call through to the parent to proceed normally
                return addressValidationForm.buildOriginalAddress.call(this, originalAddress);

            }
        });
    }
);
