<?php
/**
 * Gravity Wiz // Gravity Forms // Show Unselected Choices
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Show unselectecd choices alongside selected choices in your Checkbox and Radio Button field output.
 * This includes support for the {all_fields} merge tag, individual merge tags, and columns in Entry Blocks.
 *
 * Instructions:
 *
 * 1. Install this snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 */
add_filter( 'gform_merge_tag_filter', function( $value, $input_id, $modifier, $field, $raw_value, $format ) {
	if ( in_array( $field->get_input_type(), array( 'checkbox', 'radio' ) ) ) {
		$value = gpeb_show_unselected_choices( $field, $raw_value );
	}
	return $value;
}, 10, 6 );

add_filter( 'gpeb_checkbox_display_value', function( $value, $field, $form, $entry ) {
	return gpeb_show_unselected_choices( $field, GFFormsModel::get_lead_field_value( $entry, $field ) );
}, 10, 4 );

add_filter( 'gpeb_radio_display_value', function( $value, $field, $form, $entry ) {
	return gpeb_show_unselected_choices( $field, GFFormsModel::get_lead_field_value( $entry, $field ) );
}, 10, 4 );

function gpeb_show_unselected_choices( $field, $raw_value ) {

	$output = array();

	if ( ! empty( $field->inputs ) ) {
		foreach ( $field->inputs as $input ) {
			$class = 'gpeb-unselected';
			$icon  = '✘';
			if ( ! rgblank( $raw_value[ $input['id'] ] ) ) {
				$icon = '✔';
				$class = 'gpeb-selected';
			}
			$output[] = sprintf( '<li class="%s">%s %s</li>', $class, $icon, $input['label'] );
		}
	} else {
		foreach ( $field->choices as $choice ) {
			$class = 'gpeb-unselected';
			$icon  = '✘';
			if ( $choice['value'] === $raw_value ) {
				$icon = '✔';
				$class = 'gpeb-selected';
			}
			$output[] = sprintf( '<li class="%s">%s %s</li>', $class, $icon, $choice['text'] );
		}
	}

	return sprintf( '<ul class="gpeb-show-unselected-choices">%s</ul>', implode( "\n", $output ) );
}
