<?php
/**
 * Gravity Perks // Media Library // Add A Size to the List
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'gpml_merge_tag_image_sizes', function( $sizes ) {
	$sizes[] = 'image_one';
} );
