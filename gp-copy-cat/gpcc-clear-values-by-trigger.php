<?php
/**
 * Gravity Perks // Copy Cat // Clear Values for Specific Value
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 *
 * Instruction Video: https://www.loom.com/share/254783b3578b4183b482eff4519b1754
 *
 * Clear values in the target field when a specific option in a choice-based trigger field is
 * selected.
 *
 * For example, if copying a Name field to another Name field when a "Copy" option is
 * selected in a Radio Button field, use this snippet to clear the copied name when a "Clear"
 * option is selected in the same Radio Button field.
 */
// Update "123" to the ID of your form.
add_action( 'gform_pre_enqueue_scripts_123', function() {
	?>
	<script>
		gform.addFilter( 'gpcc_copied_value', function( value, $targetElem, field ) {
			// Update "4" to the field ID of your choice-based trigger field.
			var triggerFieldId = 4;
			if ( triggerFieldId == field.trigger && jQuery( '#choice_{0}_{1}_1'.format( field.targetFormId, triggerFieldId ) ).is( ':checked' ) ) {
				value = '';
			}
			return value;
		} );
	</script>
	<?php
} );
