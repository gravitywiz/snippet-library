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
// Set rules for Source - Target Copying, based on a Trigger Field with a matching value.
// Multiple rules can be set for Source - Target Copying.
var fieldPairs = [
	{ triggerFieldId: 17, targetFieldId: 16, sourceFieldId: 14 },
	{ triggerFieldId: 19, targetFieldId: 20, sourceFieldId: 21 },
];
var matchingValue = '1';

gform.addFilter( 'gpcc_copied_value', function( value, $targetElem, field ) {
	fieldPairs.forEach( function( pair ) {
		if ( field['target'] == pair.targetFieldId && jQuery( '#input_{0}_{1}'.gformFormat( field.targetFormId, pair.triggerFieldId ) ).val() != matchingValue ) {
			value = '';
		}
	} );
	return value;
});

// Dynamic reloading of values for scenario where trigger field is changed after source field.
fieldPairs.forEach( function( pair ) {
	var $triggerField = jQuery( '#input_GFFORMID_' + pair.triggerFieldId );
	var $sourceField  = jQuery( '#input_GFFORMID_' + pair.sourceFieldId );

	$triggerField.on( 'change', function() {
		var sourceFieldValue = $sourceField.val();
		$sourceField.val( sourceFieldValue ).trigger( 'change' );
	});
});
