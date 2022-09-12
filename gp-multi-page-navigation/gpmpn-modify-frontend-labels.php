<?php
/**
 * Gravity Perks // Multi-page Navigation // Modify Frontend Labels
 * http://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 */
add_filter( 'gpmpn_frontend_labels', function( $labels ) {
	$labels['backToLastPage'] = 'My Custom Button Label';
	$labels['submit'] = 'Submit';
	$labels['nextPageWithErrors'] = 'Next Page with Errors';
	return $labels;
} );
