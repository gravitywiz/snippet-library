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
		add_filter( 'gform_is_valid_notification_to', array( $this, 'allow_multiple_modifiers_in_notification_to_merge_tags' ), 10, 4 );
		add_filter( 'gform_pre_replace_merge_tags', array( $this, 'remove_tinyml_modifier' ) );

	}

	public function send_individual_notifications( $notification, $form, $entry ) {
		global $_gwtml_sending_notifications;

		if ( $_gwtml_sending_notifications ) {
			return $notification;
		}

		// Only execute if our custom modifier is specified on any merge tag in the "to" address.
		if ( strpos( $notification['to'], 'tinyml' ) === false ) {
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

	/**
	 * Gravity Forms doesn't support multiple modifiers in the "Send to Email" setting with their official
	 * comma-delimited format. Bypass validation if our "tinyml" modifier is present.
	 *
	 * @param $is_valid
	 * @param $to_type
	 * @param $value
	 * @param $to_field
	 *
	 * @return bool|mixed
	 */
	public function allow_multiple_modifiers_in_notification_to_merge_tags( $is_valid, $to_type, $value, $to_field ) {

		// Bypass validation if our modifier is present.
		// @todo Beef this up to actually validate that all emails and merge tags are valid.
		if ( $to_type === 'email' && strpos( $value, 'tinyml' ) ) {
			$is_valid = true;
		}

		return $is_valid;
	}

	/**
	 * Gravity Forms doesn't support multiple modifiers on choice-based field types. Replace our modifier so it doesn't
	 * conflict if there is another parameter present.
	 *
	 * For example, if we had ":value,tinyml" modifiers, the :value modifier would not be recognized when Gravity Forms
	 * determined what to return for the field value.
	 *
	 * @param $text
	 *
	 * @return array|string|string[]|null
	 */
	public function remove_tinyml_modifier( $text ) {
		return preg_replace( '/,?tinyml,?/', '', $text );
	}

}

# Configuration

new GW_Tiny_Mailing_List();
