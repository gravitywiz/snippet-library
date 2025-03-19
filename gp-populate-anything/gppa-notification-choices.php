<?php
/**
 * Gravity Perks // Populate Anything // Populate Notification as Choices & Send on Submission
 * http://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/8b133c4c75044eec96e0398cc4e7c5b9
 *
 * Populate notifications available for a selected entry and send the selected notification when the form is submitted.
 *
 * Plugin Name:  Populate Anything – Notification Choices
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Populate notifications available for a selected entry and send the selected notification when the form is submitted.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GPPA_Notification_Choices {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'               => null,
			'entry_field_id'        => null,
			'notification_field_id' => null,
		) );

		add_filter( 'gppa_input_choices', array( $this, 'modify_choices' ), 10, 2 );
		add_filter( 'gppa_array_value_to_text', array( $this, 'suppress_array_to_string_errors_for_nested_arrays' ), 9, 3 );
		add_action( 'gform_after_submission', array( $this, 'trigger_notification' ) );

	}

	public function modify_choices( $choices, $field ) {

		if ( ! $this->is_notification_choices_field( $field ) ) {
			return $choices;
		}

		$notifications = rgars( $choices, '0/object/notifications' );
		if ( ! $notifications ) {
			return $choices;
		}

		$notifications = json_decode( $notifications, ARRAY_A );
		$new_choices   = array();

		foreach ( $notifications as $notification ) {
			$new_choices[] = array(
				'text'       => $notification['name'],
				'value'      => $notification['id'],
				'isSelected' => false,
			);
		}

		return $new_choices;
	}

	public function suppress_array_to_string_errors_for_nested_arrays( $text_value, $array_value, $field ) {
		static $current_field_id;

		if ( ! empty( $current_field_id ) && $current_field_id !== $field->id && ! has_filter( 'gppa_array_value_to_text', array( $this, 'use_commas_for_arrays' ) ) ) {
			add_filter( 'gppa_array_value_to_text', array( gp_populate_anything(), 'use_commas_for_arrays' ), 10, 6 );
		} elseif ( is_array( $array_value ) && is_array( array_pop( $array_value ) ) ) {
			remove_filter( 'gppa_array_value_to_text', array( gp_populate_anything(), 'use_commas_for_arrays' ) );
		}

		$current_field_id = $field->id;

		return $text_value;
	}

	public function trigger_notification( $entry ) {
		if ( $this->is_applicable_form( $entry['form_id'] ) ) {
			$this->trigger_notification_for_entry( $entry[1], $entry[3] );
		}
	}

	/**
	 * Trigger a specific Gravity Forms notification for a given entry.
	 *
	 * @param int $entry_id The ID of the Gravity Forms entry.
	 * @param int $notification_id The ID of the notification to trigger.
	 *
	 * @return bool
	 */
	public function trigger_notification_for_entry( $entry_id, $notification_id ) {

		$entry = GFAPI::get_entry( $entry_id );
		if ( ! $entry ) {
			return false;
		}

		$form = GFAPI::get_form( $entry['form_id'] );

		GFCommon::send_notifications( array( $notification_id ), $form, $entry, false );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

	public function is_notification_choices_field( $field ) {
		return (int) $this->_args['notification_field_id'] === (int) $field->id && (int) $this->_args['form_id'] === (int) $field->formId;
	}

}

# Configuration

new GPPA_Notification_Choices( array(
	'form_id'               => 123,
	'entry_field_id'        => 4,
	'notification_field_id' => 5,
) );
