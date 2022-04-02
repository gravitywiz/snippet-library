/**
 * Gravity Perks // Entry Blocks // Show Admins All Entries
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * If your Entries block is configured to only show users their own entries, this snippet will allow you to show users
 * with the "Administrator" role all entries.
 */
add_filter( 'gpeb_entries_query', function( $processed_filter_groups, $form_id, $block_context ) {
	if ( current_user_can( 'administrator' ) ) {
		$processed_filter_groups = array();
	}
	return $processed_filter_groups;
}, 10, 3 );
