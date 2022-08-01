<?php
/**
 * Gravity Perks // GP Populate Anything // Custom GF_Entry Query Cache
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * This filter may improve performance but is known to return incorrect results when multiple fields are populated and chained to each other.
 */
// Replace "123" with your form ID and "4" with your field ID.
add_filter( 'gppa_query_limit_123_4', function() {
	// Update "1000" to whatever you would like the query limit to be.
	return 1000;
} );
