/**
 * Gravity Perks // Easy Passthrough // Populate Choice Label
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * When mapping a choice-based field to a Single Line Text field, you may want to populate the choice label rather than
 * the choice text. This snippet will handle this automatically.
 */
add_filter( 'gpep_target_field_value', function( $value, $form_id, $target_field_id, $source_field ) {

	$target_field = GFAPI::get_field( $form_id, $target_field_id );
	if ( $target_field->get_input_type() !== 'text' ) {
		return $value;
	}

	if ( empty ( $source_field->choices ) ) {
		return $value;
	}

	$choice = $source_field->get_selected_choice( $value );
	$value  = $choice['text'];

	return $value;
}, 10, 4 );
