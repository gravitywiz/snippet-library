<?php
/**
 * Gravity Perks // Post Content Merge Tags // Lock Down by Entry Creator
 * https://gravitywiz.com/documentation/gravity-forms-post-content-merge-tags/
 */
add_action( 'wp', function() {
	if ( ! is_callable( 'gp_post_content_merge_tags' ) ) {
		return;
	}
	$entry = gp_post_content_merge_tags()->get_entry();
	if ( ! $entry ) {
		return;
	}
	if ( get_current_user_id() !== (int) $entry['created_by'] && ! current_user_can( 'gform_edit_entries' ) ) {
		die( 'Bad boy!' );
	}
} );
