<?php
/**
 * Gravity Perks // Entry Blocks // Process Feeds on Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * By default, some feeds (such as asynchronous feeds) may not be processed when editing entries. This snippet will
 * disable async feed processing and then loop through all of the registered add-ons and process their feeds.
 *
 * Note, delayed feeds will still not be processed.
 */
add_filter( 'gform_entry_post_save', function( $entry, $form ) {
	if ( ! function_exists( 'gp_entry_blocks' ) ) {
		return $entry;
	}

	if ( ! gp_entry_blocks()->block_edit_form->has_submitted_edited_entry() ) {
		return $entry;
	}

	/**
	 * Disable asynchronous feed process on edit otherwise async feeds will not be re-ran due to a check in
	 * class-gf-feed-processor.php that checks `gform_get_meta( $entry_id, 'processed_feeds' )` and there isn't
	 * a way to bypass it.
	 */
	$filter_priority = rand( 100000, 999999 );
	add_filter( 'gform_is_feed_asynchronous', '__return_false', $filter_priority );

	$addons = \GFAddon::get_registered_addons();

	foreach ( $addons as $addon ) {
		$addon = call_user_func( array( $addon, 'get_instance' ) );
		if ( $addon instanceof \GFFeedAddOn ) {
			$addon->maybe_d( $entry, $form );
		}
	}

	remove_filter( 'gform_is_feed_asynchronous', '__return_false', $filter_priority );

	return $entry;
}, 10, 2 );

