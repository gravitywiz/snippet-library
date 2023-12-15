<?php
/**
 * Gravity Perks // Media Library // Return All Available Image Sizes For Image Merge Tags
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'gpml_merge_tag_image_sizes', function( $sizes ) {
	return get_intermediate_image_sizes();
} );
