<?php
/**
 * Gravity Perks // GP Populate Anything // Change Query Limit for a Specific Field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_query_limit_123_4', function( $query_limit, $object_type ) {
	// Update "1000" to the maximum number of results that should be returned for the query populating this field.
	return 1000;
}, 10, 2 );
