<?php
/**
 * Gravity Perks // Multi-Page Navigation // Change the “Next Page with Errors” Button Label
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 */
add_filter( 'gpmpn_frontend_labels', function( $labels ) {
	// Change next page with errors button to be more verbose.
	$labels['nextPageWithErrors'] = 'Skip to the next page with errors';
	return $labels;
} );
