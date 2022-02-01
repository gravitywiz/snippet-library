<?php
/**
 * Gravity Wiz // Gravity Forms // Convert Time Field Inputs to Drop Downs
 * https://gravitywiz.com/
 *
 * @see [Screenshot](https://gwiz.io/3AQZKdF)
 */
// Update "123" to your form ID and "4" to your Time field ID.
add_filter( 'gform_field_content_123_4', function( $field_content, $field, $value, $entry_id, $form_id ) {

	preg_match_all( '/<input type=\'number\'.+?>/', $field_content, $matches, PREG_SET_ORDER );

	$hours = range( 1, 12 );
	$minutes = range( 0, 45, 15 );
	$hour_options = $minute_options = array();
	$template = '<option value="%1$s">%1$s</option>';

	foreach ( $hours as $hour ) {
		$hour_options[] = sprintf( $template, str_pad( $hour, 2, 0, STR_PAD_LEFT ) );
	}

	foreach ( $minutes as $minute ) {
		$minute_options[] = sprintf( $template, str_pad( $minute, 2, 0, STR_PAD_LEFT ) );
	}

	$replacements = array(
		sprintf( '<select name="input_%1$d[]" id="input_%2$d_%1$d_%3$d">%4$s</select>', $field->id, $field->formId, 1, implode( '', $hour_options ) ),
		sprintf( '<select name="input_%1$d[]" id="input_%2$d_%1$d_%3$d">%4$s</select>', $field->id, $field->formId, 2, implode( '', $minute_options ) ),
	);

	foreach ( $matches as $index => $match ) {
		$field_content = str_replace( $match, $replacements[ $index ], $field_content );
	}

	return $field_content;
}, 10, 5 );
