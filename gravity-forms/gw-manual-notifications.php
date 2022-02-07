<?php
/**
 * Gravity Wiz // Gravity Forms // Send Manual Notifications
 * https://gravitywiz.com/send-manual-notifications-with-gravity-forms/
 *
 * Provides a custom notification event that allows you to create notifications that can be sent
 * manually (via Gravity Forms "Resend Notifications" feature).
 *
 * Plugin Name:  Gravity Wiz // Gravity Forms // Send Manual Notifications
 * Plugin URI:   https://gravitywiz.com/send-manual-notifications-with-gravity-forms/
 * Description:  Create custom notification event that can be sent manually.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
class GW_Manual_Notifications {

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {

		add_filter( 'gform_notification_events', array( $this, 'add_manual_notification_event' ) );

		add_filter( 'gform_before_resend_notifications', array( $this, 'add_notification_filter' ) );

	}

	public function add_notification_filter( $form ) {
		add_filter( 'gform_notification', array( $this, 'evaluate_notification_conditional_logic' ), 10, 3 );
		return $form;
	}

	public function add_manual_notification_event( $events ) {
		$events['manual'] = __( 'Send Manually' );
		return $events;
	}

	public function evaluate_notification_conditional_logic( $notification, $form, $entry ) {

		// if it fails conditional logic, suppress it
		if ( $notification['event'] == 'manual' && ! GFCommon::evaluate_conditional_logic( rgar( $notification, 'conditionalLogic' ), $form, $entry ) ) {
			add_filter( 'gform_pre_send_email', array( $this, 'abort_next_notification' ) );
		}

		return $notification;
	}

	public function abort_next_notification( $args ) {
		remove_filter( 'gform_pre_send_email', array( $this, 'abort_next_notification' ) );
		$args['abort_email'] = true;
		return $args;
	}

}

function gw_manual_notifications() {
	return GW_Manual_Notifications::get_instance();
}

gw_manual_notifications();
