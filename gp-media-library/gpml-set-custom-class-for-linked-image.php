<?php
/**
 * Gravity Perks // Media Library // Set a Custom Class For the Linked Image
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
add_filter( 'gpml_image_merge_tag_link_atts', function( $link_atts ) {
	//Update "custom-class" to your preferred class name.
	$link_atts['class'] = 'custom-class';

	return $link_atts;
}, 10, 3);
