<?php
/**
 * Gravity Perks // Media library // Attach Files to Source Post
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 *
 * Attach GPML-imported files to the post from which the entry was submitted.
 */
add_filter( 'gpml_media_data', function( $media, $field, $entry ) {
	$media['post_parent'] = url_to_postid( $entry['source_url'] );
	return $media;
}, 10, 3 );
