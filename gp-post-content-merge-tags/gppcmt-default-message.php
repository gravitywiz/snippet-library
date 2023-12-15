<?php
/**
 * Gravity Wiz // Post Content Merge Tags // Display a Default Message When Merge Tags Are Not Replaced
 * https://gravitywiz.com/documentation/gravity-forms-post-content-merge-tags/
 */
add_filter( 'the_content', function( $content ) {
	if ( GFCommon::has_merge_tag( $content ) ) {
		$content = 'Default message.';
	}
	return $content;
} );
