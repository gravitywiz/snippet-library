<?php
/**
 * Gravity Perks // GP Limit Dates // Auto-block Selected Dates when No Duplicates Enabled
 * http://gravitywiz.com/documentation/gp-limit-dates/
 */
// Update "123" in the filter name to your form ID and "1" to your field ID.
add_filter( 'gpld_limit_dates_options_123_1', function( $options, $form, $field ) {
	global $wpdb;

	$results        = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT meta_value FROM {$wpdb->prefix}gf_entry_meta WHERE form_id = %d AND meta_key = %d", $form['id'], $field->id ) );
	$reserved_dates = wp_list_pluck( $results, 'meta_value' );

	foreach ( $reserved_dates as $reserved_date ) {
		$options['exceptions'][] = date( 'm/d/Y', strtotime( $reserved_date ) );
	}

	return $options;
}, 10, 3 );
