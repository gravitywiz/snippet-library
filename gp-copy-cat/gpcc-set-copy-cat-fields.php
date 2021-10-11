<?php
/**
 * Gravity Perks // Copy Cat // Filter Copy Cat Fields
 * https://gravityperks.com/
 *
 * Add copy cat class to fields programmatically.
 */
add_filter( 'gpcc_copy_cat_fields_126', function( $fields, $form ) {

	// Update 44 to the ID of the field that would have the Copy Cat Class
	$fields[44] = array(
		array(
			'source'       => 44,  // Update 44 to the ID of the Source Field
			'target'       => 45,  // Update 45 to the ID of the Target Field.
			'sourceFormId' => $form['id'],
			'targetFormId' => $form['id'],
		),
	);

	return $fields;
}, 10, 2 );

