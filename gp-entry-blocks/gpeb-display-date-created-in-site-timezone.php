<?php
/**
 * Gravity Perks // Entry Blocks // Display Entry's Date Created in Site Timezone
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 */
add_filter( 'gpeb_entry', function( $entry ) {
	// Convert entry's updated date to WordPress timezone and change format.
	$entry['date_updated'] = get_date_from_gmt( $entry['date_updated'], 'Y-m-d H:i:s' );
	return $entry;
} );
