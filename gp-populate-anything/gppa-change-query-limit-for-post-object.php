<?php
/**
 * Gravity Perks // Populate Anything // Change Query Limit for a Specific Object Type
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_query_limit', function( $query_limit, $object_type ) {
	// Update "post" to your the object for which you would like to increase the limit.
	if ( $object_type->id === 'post' ) {
		// Update "750" to the number of results to return for this object type.
		$query_limit = 750;
	}
	return $query_limit;
}, 10, 2 );
