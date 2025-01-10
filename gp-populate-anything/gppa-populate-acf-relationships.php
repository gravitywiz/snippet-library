<?php
/**
 * Gravity Perks // Populate Anything // Populate ACF Relationships
 *
 * Populate each related post as a separate choice when populating data from an ACF Relationship custom field.
 *
 * Plugin Name:  GPPA ACF Relationships
 * Plugin URI:   http:///gravitywiz.com.com/documentation/gravity-forms-populate-anything/
 * Description:  Populate each related post as a separate choice when populating data from an ACF Relationship custom field.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   http://gravitywiz.com
 */
add_filter( 'gppa_input_choices', function( $choices, $field ) {

	if ( strpos( $field->cssClass, 'gppa-acf-relationships' ) === false ) {
		return $choices;
	}

	if ( rgars( $choices, '0/gppaErrorChoice' ) ) {
		return $choices;
	}

	$new_choices = array();

	foreach ( $choices as $choice ) {
		$post_ids = array_filter( explode( ',', $choice['value'] ) );

		foreach ( $post_ids as $post_id ) {
			$new_choices[] = array(
				'value' => $post_id,
				'text'  => get_the_title( $post_id ),
			);
		}
	}

	return $new_choices;
}, 10, 2 );
