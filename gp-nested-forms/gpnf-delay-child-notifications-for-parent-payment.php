<?php
/**
 * Gravity Wiz // Nested Forms // Delay Child Notifications for Parent Payment
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
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

	private $args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( ! is_callable( 'gpnf_notification_processing' ) ) {
			return;
		}

		add_filter( 'gpnf_should_send_notification', array( $this, 'gpnf_should_send_notification' ), 10, 7 );
		add_action( 'gform_post_payment_completed', array( $this, 'gform_post_payment_completed' ) );
		// Removing this filter causes the original issue of double notification to occur, see HS#23899 PR #85
		remove_filter( 'gform_entry_post_save', array( gpnf_notification_processing(), 'maybe_send_child_notifications' ), 11 );
		add_filter( 'gform_entry_post_save', array( $this, 'send_notifications_for_non_payment_entries' ), 12, 2 );
	}

	public function gpnf_should_send_notification( $should_send_notification, $notification, $context, $parent_form, $nested_form_field, $entry, $child_form ) {
		if ( $context === 'parent' && $this->is_applicable_form( $parent_form['id'] ) ) {
			$parent_entry             = GFAPI::get_entry( rgar( $entry, 'gpnf_entry_parent' ) );
			$should_send_notification = in_array( rgar( $parent_entry, 'payment_status' ), array( 'Paid', 'Active' ), true );
		}

		return $should_send_notification;

	}

	public function gform_post_payment_completed( $entry ) {
		if ( is_callable( 'gpnf_notification_processing' ) && $this->is_applicable_form( $entry['form_id'] ) ) {
			gpnf_notification_processing()->maybe_send_child_notifications( $entry, GFAPI::get_form( $entry['form_id'] ) );
		}
	}

	public function send_notifications_for_non_payment_entries( $entry, $form ) {

		if ( ! $this->is_applicable_form( $form['id'] ) ) {
			return $entry;
		}

		$_entry = GFAPI::get_entry( $entry['id'] );
		if ( ! $_entry['payment_status'] ) {
			gpnf_notification_processing()->maybe_send_child_notifications( $entry, $form );
		}

		return $entry;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

// Configuration
new GW_GPNF_Delay_Child_Notifications( array(
	'form_id' => 3, // Set this to the parent form ID
) );
