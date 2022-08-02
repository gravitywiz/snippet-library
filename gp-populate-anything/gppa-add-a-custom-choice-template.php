<?php
/**
 * Gravity Perks // Populate Anything // Add A Custom Choice Template
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Choice templates allow you to map data to different properties of your Gravity Forms choices. In this example, we
 * demonstrate how to create a new template for a theoretical "image" choice property. With this in place, you would
 * be able to select a value in the Populate Anything choices UI that is mapped to the "image" property for each choice.
 */
add_filter( 'gppa_input_choice', function( $choice, $field, $object, $objects ) {
	$choice['image'] = gp_populate_anything()->process_template( $field, 'image', $object, 'choices', $objects );
	return $choice;
}, 10, 4 );
