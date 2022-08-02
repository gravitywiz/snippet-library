<?php
/**
 * Gravity Perks // Media Library // Add A Custom Size
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'gpml_merge_tag_image_sizes', function( $sizes ) {
	return array( 'image_one', 'image_two' );
} );
