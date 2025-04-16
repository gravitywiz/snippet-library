<?php
/**
 * Gravity Perks // Easy Passthrough // Passthrough for Consent Fields.
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * Instruction Video: https://www.loom.com/share/209126dc84ab483b9025c040f5adf450
 *
 * Passes through the value of a consent field from the source form to the target form.
 */
add_filter( 'gform_pre_render', function( $form ) {
    // Update to the source and target consent field IDs and target form ID.
	$source_consent_field_id = '4';
	$target_consent_field_id = '4';
	$target_form_id          = '80';

	if ( $form['id'] != $target_form_id ) {
		return $form;
	}

	if ( rgget( 'ep_token' ) && is_callable( 'gp_easy_passthrough' ) ) {
		$entry = gp_easy_passthrough()->get_entry_for_token( rgget( 'ep_token' ) );

		foreach ( $form['fields'] as $field ) {
			if ( $field->type == 'consent' && $field->id == $target_consent_field_id && $entry[ $source_consent_field_id . '.1' ] == '1' ) {
				?>
				<script type="text/javascript">
					document.addEventListener( 'DOMContentLoaded', function() {
						var consentCheckbox = document.querySelector( 'input[name="input_<?php echo $field->id; ?>.1"] ');

						if ( consentCheckbox ) {
							consentCheckbox.checked = true;
						}
					});
				</script>
				<?php
			}
		}
	}
	return $form;
}, 12, 1 );
