<?php
/**
 * Gravity Perks // Populate Anything // Convert Arrays To A Comma Separated List
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_object_merge_tag_replacement_value', function( $replace, $object, $match ) {
	return is_array( $replace ) ? join( ', ', $replace ) : $replace;
}, 10, 3 );
