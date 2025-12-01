<?php
/**
 * Gravity Wiz // Gravity Forms // Default Choice Merge Tags to Values
 * https://gravitywiz.com
 *
 * Forces choice-based merge tags to return values unless a modifier is already set.
 */
add_filter( 'gform_pre_replace_merge_tags', function ( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

	// Set specific form IDs to target, or leave empty for all forms.
	$target_form_ids = array(); // e.g. array( 1, 3, 5 )

	if ( $target_form_ids && ! in_array( (int) rgar( $form, 'id' ), $target_form_ids, true ) ) {
		return $text;
	}

	preg_match_all( '/{[^{}]*?:(\d+(?:\.\d+)?)(?::([^}]*))?}/', $text, $matches, PREG_SET_ORDER );

	foreach ( $matches as $match ) {
		$field_id = $match[1];
		$field    = GFAPI::get_field( $form, $field_id );

		if ( ! $field || ! in_array( $field->get_input_type(), array( 'select', 'radio', 'checkbox', 'multiselect', 'option', 'product', 'poll', 'survey', 'quiz', 'post_category' ), true ) ) {
			continue;
		}

		$modifiers = isset( $match[2] ) && $match[2] !== '' ? array_map( 'trim', array_map( 'strtolower', explode( ',', $match[2] ) ) ) : array();

		// If any modifier is already present, honor it.
		if ( $modifiers ) {
			continue;
		}

		$replacement = rtrim( $match[0], '}' ) . ( $match[2] === '' ? ':value}' : ',value}' );
		$text        = str_replace( $match[0], $replacement, $text );
	}

	return $text;
}, 9, 7 );
