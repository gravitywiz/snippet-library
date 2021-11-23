/**
* Gravity Perks // Copy Cat // Copy Label (instead of Value)
* https://gravitywiz.com/documentation/gravity-forms-copy-cat/
*
* By default, the value of a choice-based field is copied.
* Use this snippet to copy the label instead of the value.
*
* Instructions:
* 1. Install our free Custom Javascript for Gravity Forms plugin. 
* Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
* 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
*/
gform.addFilter( 'gppc_copied_value', function( value, $elem, data ) {
    $source = jQuery( '#input_' + data.sourceFormId + '_' + data.source );
    if( $source.is( 'select' ) ) {
        value = $source.find( 'option:selected' ).text();
    }
    return value;
} );
