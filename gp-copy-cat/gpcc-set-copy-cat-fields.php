<?php
/**
 * Gravity Perks // Copy Cat // Filter Copy Cat Fields
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 *
 * Add copy cat class to fields programmatically.
 */
// Update 123 to your form ID.
add_filter( 'gpcc_copy_cat_fields_123', function( $fields, $form ) {

	// Update 4 to the ID of the field that would have the Copy Cat Class
	$fields[4] = array(
		array(
			'source'       => 4,  // Update 4 to the ID of the source field.
			'target'       => 5,  // Update 5 to the ID of the target field.
			'sourceFormId' => $form['id'],
			'targetFormId' => $form['id'],
		),
	);

	return $fields;
}, 10, 2 );
