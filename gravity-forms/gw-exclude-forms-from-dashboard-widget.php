<?php
/**
 * Gravity Wiz // Gravity Forms // Exclude Specific Forms from Gravity Forms Dashboard Widget
 * https://gravitywiz.com/
 *
 * STOP! If you’re looking for a way to include or exclude specific forms in your dashboard widget,
 * check out our free Dashboard Widget Controls plugin.
 *
 * 👉 https://github.com/gravitywiz/snippet-library/blob/master/gravity-forms/gw-dashboard-widget-controls.php
 */
add_filter( 'gform_form_summary', function( $form_summary ) {
	// Update these values to your own form IDs that should be excluded.
	$exclude_form_ids = array( 12, 23, 34 );
	foreach ( $form_summary as &$item ) {
		if ( in_array( $item['id'], $exclude_form_ids ) ) {
			$item = null;
		}
	}
	return array_filter( $form_summary );
} );
