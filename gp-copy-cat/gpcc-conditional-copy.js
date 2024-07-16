/**
* Gravity Perks // Copy Cat // Conditional Copy based on a Trigger Field Value
* https://gravitywiz.com/documentation/gravity-forms-copy-cat/
*
* Instruction Video: https://www.loom.com/share/f8db53c9359c45d8bc160d6e1d8eb4fc
*
* Instructions:
*
* 1. Install this snippet with our free Custom JavaScript plugin.
*    https://gravitywiz.com/gravity-forms-code-chest/
*
* 2. Configure the snippet per the inline instructions.
*/
gform.addFilter( 'gpcc_copied_value', function( value, $targetElem, field ) {
    // Update "17" to the field ID of your choice-based trigger field.
    var triggerFieldId = 17;

    if ( jQuery( '#input_{0}_{1}'.gformFormat( field.targetFormId, triggerFieldId ) ).val() != '1' ) {
        value = '';
    }
    return value;
} );
