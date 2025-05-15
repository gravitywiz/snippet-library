<?php
/**
 * Gravity Wiz // Gravity Forms // Consolidate Multiple List Fields into a Single List Field
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/8b45a92cf56249a982aa1aa6e1301778
 *
 * This snippet merges values from multiple list fields into a single list field.
 */
// Update "123" to your form ID.
add_action( 'gform_post_submission_123', function ( $entry, $form ) {
	// Define source field IDs and target field ID.
	$source_field_ids = array( 1, 5, 4 );
	$target_field_id  = 7;

	$combined = array();

	// Loop through source field IDs and merge their unserialized values.
	foreach ( $source_field_ids as $field_id ) {
		if ( isset( $entry[ $field_id ] ) && ! empty( $entry[ $field_id ] ) ) {
			$field_values = unserialize( $entry[ $field_id ] );
			$combined	 = array_merge( $combined, $field_values );
		}
	}

	// Re-index the combined array.
	$combined = array_values( $combined );

	// Serialize the combined array and update the target field and entry.
	$finalSerialized		   = serialize( $combined );
	$entry[ $target_field_id ] = $finalSerialized;

	GFAPI::update_entry( $entry );
}, 10, 2 );
