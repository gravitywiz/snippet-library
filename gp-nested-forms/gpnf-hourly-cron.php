<?php

/**
 * Gravity Perks // Nested Forms // Custom Cron & Orphaned Entry Expiration
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions:
 *   1. Choose from one of the configurations below.
 *   2. Ensure that whichever configurations you're not using are commented out.
 */
class GPNF_Custom_Orphaned_Entry_Expiration {

	public $expiration;
	public $cron_interval;

	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'expiration'    => HOUR_IN_SECONDS,
			'cron_interval' => 'hourly',
		) );

		$this->expiration    = $args['expiration'];
		$this->cron_interval = $args['cron_interval'];

		$this->schedule();

		add_action( 'gpnf_custom_cron', array( $this, 'cron' ) );
		add_filter( 'gpnf_expiration_modifier', array( $this, 'expiration_modifier' ) );
	}

	public function schedule() {
		if ( ! wp_next_scheduled( 'gpnf_custom_cron' ) ) {
			wp_schedule_event( time(), $this->cron_interval, 'gpnf_custom_cron' );
		}
	}

	public function cron() {
		if ( ! is_callable( 'gp_nested_forms' ) ) {
			return;
		}

		gp_nested_forms()->daily_cron();
	}

	public function expiration_modifier() {
		return $this->expiration;
	}

}

/*
 * Default configuration: configure with interval of hourly with expiration of 1 hour
 */
new GPNF_Custom_Orphaned_Entry_Expiration();

/*
 * Example configuration: configure with custom expiration and built-in cron interval (twice daily)
 *
 * See https://developer.wordpress.org/reference/functions/wp_get_schedules/ for built-in cron intervals/schedules.
 */

// Configure with custom expiration and built-in cron interval (twice daily)
//new GPNF_Custom_Orphaned_Entry_Expiration( array(
//	'expiration' => 60 * 60 * 4, // 4 hours
//	'interval'   => 'twicedaily',
//) );

/*
 * Example configuration: configure with custom expiration and custom cron interval
 */

// Add custom cron schedule of every 5 minutes so it can be used by the GPNF_Custom_Orphaned_Entry_Expiration class.
//add_filter( 'cron_schedules', function ( $schedules ) {
//	// Adds every five minutes to the existing schedules.
//	$schedules['everyfiveminutes'] = array(
//		'interval' => 60 * 5,
//		'display'  => 'Every Five Minutes',
//	);
//
//	return $schedules;
//} );
//
//new GPNF_Custom_Orphaned_Entry_Expiration( array(
//	'expiration'    => 60 * 5, // 5 Minutes
//	'cron_interval' => 'everyfiveminutes',
//) );
