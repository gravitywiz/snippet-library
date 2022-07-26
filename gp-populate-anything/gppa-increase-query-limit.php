<?php
/**
 * Gravity Perks // GP Populate Anything // Change The Query Limit
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
// Replace "123" with your form ID and "4" with your field ID.
add_filter( 'gppa_query_limit_123_4', function() {
	// Update "1000" to whatever you would like the query limit to be.
	return 1000;
} );
