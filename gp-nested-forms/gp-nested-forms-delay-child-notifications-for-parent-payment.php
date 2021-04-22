<?php
/**
 * Gravity Wiz // Nested Forms // Delay Child Notifications for Parent Payment
 * https://github.com/gravitywiz/snippet-library/blob/master/gw-snippet-template.php
 *
 * Delay GP Nested Forms child form notification until payment processing is completed.
 *
 * Plugin Name:  Delay Child Notifications for Parent Payment
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Delay GP Nested Forms child form notification until payment processing is completed.
 * Author:       Gravity Wiz
 * Version:      1.1
 * Author URI:   http://gravitywiz.com
 */
class GW_GPNF_Delay_Child_Notifications {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_ids' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gpnf_should_send_notification', array( $this, 'gpnf_should_send_notification' ), 10, 7 );
		add_action( 'gform_post_payment_completed', array( $this, 'gform_post_payment_completed' ) );
		remove_filter( 'gform_entry_post_save', array( gpnf_notification_processing(), 'maybe_send_child_notifications' ), 11 );

	}

	public function gpnf_should_send_notification( $should_send_notification, $notification, $context, $parent_form, $nested_form_field, $entry, $child_form ) {

		if ( $context === 'parent' ) {
			$parent_entry             = GFAPI::get_entry( rgar( $entry, 'gpnf_entry_parent' ) );
			$should_send_notification = in_array( rgar( $parent_entry, 'payment_status' ), array( 'Paid', 'Active' ), true );
		}
		// Prevent double notifications for non-delayed payment gateways.
		remove_filter( 'gform_post_payment_completed', array( $this, 'gpnf_should_send_notification' ) );
		return $should_send_notification;

	}

	public function gform_post_payment_completed( $entry ) {

		if ( is_callable( 'gpnf_notification_processing' ) ) {
			gpnf_notification_processing()->maybe_send_child_notifications( $entry, GFAPI::get_form( $entry['form_id'] ) );
		}

	}

}

// Configuration
new GW_GPNF_Delay_Child_Notifications( array(
	'form_ids' => array( 3 ), // Add all parent form IDs to this array
) );
