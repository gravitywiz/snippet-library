<?php
/**
 * Gravity Perks // Populate Anything // Filter by Created By on Entry Edit
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * When filtering by the special Current User ID value, you may want to preserve the original context when editing an
 * entry by returning the ID of the user who created the entry rather than the current user editing the entry.
 */
add_filter( 'gform_replace_merge_tags', function( $value ) {
	if ( GFForms::get_page() === 'entry_detail_edit' ) {
		$entry = GFAPI::get_entry( rgget( 'lid' ) );
		if ( ! is_wp_error( $entry ) ) {
			$value = str_replace( 'special_value:current_user:ID', $entry['created_by'], $value );
		}
	}
	return $value;
} );
