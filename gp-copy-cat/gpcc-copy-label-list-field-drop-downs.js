/**
* Gravity Perks // Copy Cat // Copy Label for List Field Drop Downs
* https://gravitywiz.com/documentation/gravity-forms-copy-cat/
*
* Copy the label (rather than the value) for Drop Downs in List fields.
*
* Instructions:
* 1. Install our free Custom Javascript for Gravity Forms plugin. 
*    Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
* 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
*/
gform.addFilter( 'gppc_copied_value', function( value, $elem, data ) {
  if ( $.isNumeric( value ) ) {
    var found = jQuery( '#field_' + data.sourceFormId + '_' + data.source ).find( 'option[value="' + value + '"]' ).text();
    value = found ? found : value;
  }
  return value;
} );
