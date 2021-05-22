<?php
/**
 * This snippet dynamically enables the choice value setting for GPPA-enabled fields so that GravityView 
 * will present the option to show the value or label when including this field in a view.
 * 
 * See: https://secure.helpscout.net/conversation/1511770443/24411/#thread-4407403575
 */
add_filter( 'gform_form_post_get_meta', function( $form ) {

	if ( ! isset( $_GET['post'] ) || ! $_GET['post'] ) {
		return $form;
	}

	$post = get_post( $_GET['post'] );
	if ( ! $post || $post->post_type !== 'gravityview' ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {
		if ( $field->{'gppa-choices-enabled'} ) {
			$field->enableChoiceValue = true;
		}
	}
	
	return $form;
} );
