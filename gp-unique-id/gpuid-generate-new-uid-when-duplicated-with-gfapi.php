<?php
/**
 * Gravity Perks // Unique ID // Generate Unique ID when Entry is Duplicated using GFAPI.
 * https://gravitywiz.com/documentation/gp-unique-id/
 *
 * When an entry is duplicated using GFAPI, the Unique ID value is copied to the duplicated entry.
 * This snippet generates a new unique ID value for the duplicated entry.
 */
add_action( 'gform_post_add_entry', 'gw_regenerate_uid_for_duplicate_entry', 10, 2 );
function gw_regenerate_uid_for_duplicate_entry( $entry, $form ) {
	// Only process entry for a targeted form.
	if ( $form['id'] != 165 ) {
		return;
	}

	foreach ( $form['fields'] as $field ) {
		if ( is_a( $field, 'GF_Field_Unique_ID' ) ) {
			$uid = gp_unique_id()->get_unique( $form['id'], $field );
			GFAPI::update_entry_field( $entry['id'], $field->id, $uid );
			$entry[ $field->id ] = $uid;
		}
	}
}
