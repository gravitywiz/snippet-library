<?php
/**
 * Gravity Perks // Media Library // Set Uploaded Image Data
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'gpml_media_data', function( $media, $field, $entry ) {

	$media['post_data']['post_title']                            = 'Boom!';
	$media['post_data']['post_content']                          = 'This is the description.';
	$media['post_data']['post_excerpt']                          = 'This is the caption.';
	$media['post_data']['post_meta']['_wp_attachment_image_alt'] = 'This is the alt text.';

	return $media;
}, 10, 3 );
