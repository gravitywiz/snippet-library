<?php
/**
 * Gravity Wiz // Gravity Forms // Tiny Mailing List
 * http://gravitywiz.com/
 *
 * Send a notification to multiple recipients individually.
 *
 * Recipients should be specified in a Checkbox or Multi-select field or as a comma-delimited list in any text-based
 * input type. Use the populated field's merge tag in the notification's "Send To Email" setting. Use the :value
 * modifier with the merge tag if you are populating the email address as the value rather than the label.
 *
 * Note: This *should not* be used to large numbers of notifications.
 * 
 * Plugin Name:  Gravity Forms Tiny Mailing List
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Send a notification to multiple recipients individually.
 * Author:       Gravity Wiz
 * Version:      0.3
 * Author URI:   http://gravitywiz.com
 */
class GW_Tiny_Mailing_List {

	public function __construct( $args = array() ) {

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_notification', array( $this, 'send_individual_notifications' ), 10, 3 );

	}

	public function send_individual_notifications( $notification, $form, $entry ) {
		global $_gwtml_sending_notifications;

		if ( $_gwtml_sending_notifications ) {
			return $notification;
		}

		$tos = explode( ',', GFCommon::replace_variables( $notification['to'], $form, $entry, false, false, false, 'text' ) );
		if ( count( $tos ) <= 1 ) {
			return $notification;
		}

		$_gwtml_sending_notifications = true;

		foreach ( $tos as $to ) {
			$notification['to'] = $to;
			GFCommon::send_notification( $notification, $form, $entry );
		}

		$_gwtml_sending_notifications = false;

		$notification['to'] = false;

		return $notification;
	}

}

# Configuration

new GW_Tiny_Mailing_List();
