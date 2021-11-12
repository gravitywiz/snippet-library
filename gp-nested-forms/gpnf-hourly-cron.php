<?php
/**
 * Gravity Perks // Nested Forms // Hourly Cron & Orphaned Entry Expiration
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
function gpnf_custom_hourly_cron() {

	if ( ! is_callable( 'gp_nested_forms' ) ) {
		return;
	}

	if ( ! wp_next_scheduled( 'gpnf_hourly_cron' ) ) {
		wp_schedule_event( time(), 'hourly', 'gpnf_hourly_cron' );
	}

	add_action( 'gpnf_hourly_cron', array( gp_nested_forms(), 'daily_cron' ) );

}

gpnf_custom_hourly_cron();

add_filter( 'gpnf_expiration_modifier', function() {
	return HOUR_IN_SECONDS;
} );
