<?php
/**
 * Gravity Perks // Media Library // Set Uploaded Image Data by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'gpml_media_data', function( $media, $field, $entry ) {

  // Replace "1", "2", "3" and "4" in each line below with the ID of the field that will contain value that should be used for each image property.
	$media['post_data']['post_title']                            = rgpost( 'input_1' );
	$media['post_data']['post_content']                          = rgpost( 'input_2' );
	$media['post_data']['post_excerpt']                          = rgpost( 'input_3' );
	$media['post_data']['post_meta']['_wp_attachment_image_alt'] = rgpost( 'input_4' );

	return $media;
}, 10, 3 );
