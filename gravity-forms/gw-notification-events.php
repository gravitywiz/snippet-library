<?php
/**
* Gravity Wiz // Gravity Forms // Notification Events
*
* Create custom notification events for Gravity Forms. Currently supports entry field update conditions.
*
* Future support will be added for time-based notification events, entry property updates, and more.
*
* @author    David Smith <david@gravitywiz.com>
* @license   GPL-2.0+
* @link      http://gravitywiz.com/...
* @copyright 2015 Gravity Wiz
*/
class GW_Notification_Event {

	public function __construct( $args ) {
		$this->_args = wp_parse_args( $args, array(
			'form_id'     => false,
			'event_name'  => false,
			'event_slug'  => false,
			'object_type' => 'entry',
			'trigger'     => array(),
		) );

		add_action( 'plugins_loaded', array( $this, 'init' ), 15 );
	}

	public function init() {
		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		if ( ! $this->_args['event_name'] ) {
			return;
		}

		add_filter( 'gform_notification_events', array( $this, 'add_notification_event' ) );
		add_filter( 'gform_notification', array( $this, 'add_notification_sent_entry_meta' ), 10, 3 );

		$this->add_trigger_listeners();
	}

	public function add_notification_event( $events ) {

		$form_id = rgget( 'id' );
		if ( $this->is_applicable_form( $form_id ) ) {
			$events[ $this->get_event_slug() ] = $this->_args['event_name'];
		}

		return $events;
	}

	public function get_event_slug() {

		if ( $this->_args['event_slug'] ) {
			return $this->_args['event_slug'];
		}

		$slug = strtolower( str_replace( ' ', '_', $this->_args['event_name'] ) );

		return $slug;
	}

	public function add_trigger_listeners() {

		if ( is_callable( $this->_args['trigger'] ) ) {
			call_user_func( $this->_args['trigger'] );
			return;
		}

		$trigger_type = rgars( $this->_args['trigger'], 'type' );
		$func         = array( $this, "process_trigger_{$trigger_type}" );

		switch ( $trigger_type ) {
			case 'update_entry':
				add_action( 'gform_after_update_entry', $func, 10, 2 );
				break;
			case 'delete_entry':
				add_action( 'gform_delete_entry', $func, 10, 2 );
				break;
			case 'hook':
				list( $hook, $func, $priority, $parameter_count ) = array_pad( $this->_args['trigger']['args'], 4, false );

				// if no func is provided, use default naming convention: 'process_trigger_{hook}'
				if ( ! $func ) {
					$func = array( $this, "process_trigger_{$hook}" );
					// assume that any string-based func is intended to be a function in this class
				} elseif ( ! is_array( $func ) && is_callable( array( $this, $func ) ) ) {
					$func = array( $this, $func );
				}

				if ( ! is_callable( $func ) ) {
					return;
				}

				add_action( $hook, $func, $priority ? $priority : 10, $parameter_count ? $parameter_count : 1 );

				break;
		}

	}

	public function process_trigger_update_entry( $form, $entry_id ) {

		$entry = GFAPI::get_entry( $entry_id );
		if ( is_wp_error( $entry ) ) {
			return;
		}

		$this->maybe_send_notifications( $form, $entry );

	}

	public function process_trigger_delete_entry( $entry_id ) {

		$entry = GFAPI::get_entry( $entry_id );
		if ( is_wp_error( $entry ) ) {
			return;
		}

		$this->maybe_send_notifications( GFAPI::get_form( $entry['form_id'] ), $entry );

	}

	public function maybe_send_notifications( $form, $entry ) {

		$trigger_rules_met = GFCommon::evaluate_conditional_logic( $this->_args['trigger'], $form, $entry );
		if ( ! $trigger_rules_met ) {
			return;
		}

		$this->send_notifications( $this->get_event_slug(), $form, $entry );

	}

	public function send_notifications( $event, $form, $entry, $force_send = null ) {

		$notifications = GFCommon::get_notifications_to_send( $event, $form, $entry );
		$ids           = array();

		foreach ( $notifications as $notification ) {
			if ( $force_send || ! gform_get_meta( $entry['id'], 'notification_' . $notification['id'] ) ) {
				$ids[] = $notification['id'];
			}
		}

		GFCommon::send_notifications( $ids, $form, $entry, true, $event );

	}

	public function add_notification_sent_entry_meta( $notification, $form, $entry ) {

		if ( rgar( $notification, 'event' ) === $this->get_event_slug() ) {
			gform_update_meta( $entry['id'], "notification_{$notification['id']}", 1 );
		}

		return $notification;
	}

	function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Usage Example

//new GW_Notification_Event( (array(
//	'event_name'  => 'Entry is updated',
//	'object_type' => 'entry', // 'entry', 'field', 'form', 'user', etc
//	'trigger'     => array(
//		'type' => 'update_entry',
//		)
//	)
//) );

//new GW_Notification_Event( array(
//	'form_id'     => 1,
//	'event_name'  => $order_status['label'],
//	'object_type' => 'entry', // 'entry', 'field', 'form', 'user', etc
//	'trigger'     => array(
//		'type'      => 'update_entry',
//		'logicType' => 'all', // 'all' or 'any'
//		'rules'     => array(
//			array(
//				'fieldId'  => 5,
//				'operator' => 'is',
//				'value'    => $order_status['field_value']
//			)
//		)
//	)
//) );

# Advanced Usage Example

//class My_Custom_Notification_Event extends GW_Notification_Event {
//
//	public function process_trigger_gform_after_submission( $entry, $form ) {
//		$this->maybe_send_notifications( $form, $entry );
//	}
//
//}
//
//new My_Custom_Notification_Event( array(
//	'form_id'     => 387,
//	'event_name'  => 'After form is submitted',
//	'trigger'     => array(
//		'type' => 'hook',
//		'args' => array( 'gform_after_submission', false, 11, 2 )
//	)
//) );
