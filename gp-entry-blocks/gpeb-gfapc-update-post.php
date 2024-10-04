<?php
/**
 * Gravity Perks // Entry Blocks // Update Post on Entry Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Use this snippet to update the original post created by an entry when that entry is edited. By default, Gravity Forms
 * would create a new post.
 *
 * This only works with the GF Advanced Post Creation add-on.
 */
add_filter( 'gform_gravityformsadvancedpostcreation_pre_process_feeds', function( $feeds, $entry, $form ) {
	$entry_posts = gform_get_meta( $entry['id'], 'gravityformsadvancedpostcreation_post_id' );
	$gfapc_feeds = array_filter( $feeds, function( $feed ) {
		return $feed['addon_slug'] == 'gravityformsadvancedpostcreation';
	} );

	if ( is_array( $entry_posts ) ) {
		foreach ( $gfapc_feeds as $feed ) {
			$post_feed = array_values(array_filter( $entry_posts, function( $post ) use ( $feed ) {
				return $post['feed_id'] == $feed['id'];
			} ) );

			if ( ! empty( $post_feed ) && gf_advancedpostcreation()->is_feed_condition_met( $feed, $form, $entry ) ) {
				gf_advancedpostcreation()->update_post( $post_feed[0]['post_id'], $feed, $entry, $form );
			}
		}
	}

	return $feeds;
}, 10, 3 );

