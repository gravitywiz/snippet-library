<?php
/**
 * Gravity Wiz // Gravity Forms // Spam Notification
 *
 * Send notifications when an entry is marked as spam. Will only fire when the entry is marked as spam as part of the
 * initial form submission.
 *
 * Plugin Name:  GF Spam Notification
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Send notifications when an entry is marked as spam.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   http://gravitywiz.com
 *
 * @todo
 *   - Add support for generating a one-click link to ham an entry.
 *   - Add support for auto-processing feeds when an entry is hammed.
 */
class GW_Spam_Notification {

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {

		add_filter( 'gform_notification_events', array( $this, 'add_spammed_notification_event' ) );
		add_filter( 'gform_before_resend_notifications', array( $this, 'add_notification_filter' ) );

		add_action( 'gform_entry_created', array( $this, 'maybe_trigger_spammed_notification_event' ), 10, 2 );

	}

	public function add_notification_filter( $form ) {
		add_filter( 'gform_notification', array( $this, 'evaluate_notification_conditional_logic' ), 10, 3 );
		return $form;
	}

	public function add_spammed_notification_event( $events ) {
		$events['spammed'] = __( 'Entry marked as spam' );
		return $events;
	}

	public function maybe_trigger_spammed_notification_event( $entry, $form ) {
		if ( $entry['status'] === 'spam' ) {
			GFAPI::send_notifications( $form, $entry, 'spammed' );
		}
	}

	public function evaluate_notification_conditional_logic( $notification, $form, $entry ) {

		// if it fails conditional logic, suppress it
		if ( $notification['event'] === 'spammed' && ! GFCommon::evaluate_conditional_logic( rgar( $notification, 'conditionalLogic' ), $form, $entry ) ) {
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

function gw_spam_notification() {
	return GW_Spam_Notification::get_instance();
}

gw_spam_notification();
