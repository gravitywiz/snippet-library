<?php
/**
 * Gravity Perks // Populate Anything // Add Multiple Custom Fields as Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Use this snippet to dynamically populate a field with multiple custom field values.
 *
 * For example, imagine you have a booking represented by a WordPress Post object and that booking stores its deposit
 * price and full price as two separate custom fields. With this snippet, you would configure
 * your field to target the desired post and then add each option manually based on its post meta key.
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {

	$choices = array();

	// Add first choice.
	$choices[] = array(
		'text'       => 'First Dynamic Option',
		'value'      => 'first-dynamic-option',
		'price'      => get_post_meta( $objects[0]->ID, 'my_first_choice', true ),
	);

	// Add second choice.
	$choices[] = array(
		'text'       => 'Second Dynamic Option',
		'value'      => 'second-dynamic-option',
		'price'      => get_post_meta( $objects[0]->ID, 'my_second_choice', true ),
	);

	return $choices;
}, 10, 3 );
