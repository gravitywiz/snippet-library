<?php
/**
 * Gravity Perks // GP Populate Anything // Change The Query Limit
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_query_limit', function() {
	// Update "750" to whatever you would like the query limit to be.
	return 750;
} );
