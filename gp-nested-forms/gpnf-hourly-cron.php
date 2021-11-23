<?php

/**
 * Gravity Perks // Nested Forms // Custom Cron & Orphaned Entry Expiration
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
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

// Configure with default interval of hourly with expiration of 1 hour
new GPNF_Custom_Orphaned_Entry_Expiration();

// Configure with custom expiration and cron interval
//new GPNF_Custom_Orphaned_Entry_Expiration( array(
//	'expiration'    => 60 * 60 * 4, // 4 Hours
//	'cron_interval' => 'twicedaily',
//) );
