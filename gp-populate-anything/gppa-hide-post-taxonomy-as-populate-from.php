<?php
/**
 * Gravity Perks // GP Populate Anything // Hide Post And Taxonomy Term As Options To Populate From
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_autoloaded_object_types', function ( $object_types ) {
	foreach ( $object_types as $key => $value ) {
		if ( in_array( $key, array( 'post', 'term' ) ) ) {
		unset( $object_types[ $key ] );
		}
	}
	return $object_types;
}, 10, 1 );
