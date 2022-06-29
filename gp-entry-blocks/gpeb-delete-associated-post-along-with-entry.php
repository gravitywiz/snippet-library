<?php
/**
 * Gravity Perks // Entry Blocks // Delete/trash associated post along with entry
 *
 * Trash (or delete) post created by entry when an entry is trashed/deleted using GP Entry Blocks.
 *
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 */
add_action( 'gpeb_trash_entry', function( $entry ) {
	$post_id = rgar( $entry, 'post_id' );

	if ( $post_id ) {
		wp_trash_post( $post_id );
	}
} );

add_action( 'gpeb_delete_entry', function( $entry ) {
	$post_id = rgar( $entry, 'post_id' );

	if ( $post_id ) {
		wp_delete_post( $post_id, true );
	}
} );
