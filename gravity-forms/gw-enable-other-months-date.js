/**
* Gravity Wiz // Gravity Forms // Enable Other Months Dates
* https://gravitywiz.com/
*
* By default, when viewing the datepicker, any visible dates in the previous or next months are disabled.
* This snippet will enable these dates. 
*
* Instructions:
* 1. Install our free Custom Javascript for Gravity Forms plugin. 
*    Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
* 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
*/
gform.addFilter( 'gform_datepicker_options_pre_init', function( optionsObj, formId, fieldId ) {
    optionsObj.selectOtherMonths = true;
    return optionsObj;
} );
