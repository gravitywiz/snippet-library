<?php
/**
 * Gravity Perks // Media Library // Upload the Original Image and the Thumbnail
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'gpml_image_sizes', function ( $sizes ) {
	// Removing the “thumbnail” will only upload the original image.
	return array( 'thumbnail' );
} );
