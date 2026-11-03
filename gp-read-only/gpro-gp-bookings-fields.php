<?php
/**
 * Gravity Perks // Read Only // GP Bookings Field Support
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 *
 * Makes GP Bookings Resource and Booking Time fields read-only when GP Populate Anything populates a
 * value for them. If no value is populated, the field is left interactive.
 *
 * Plugin Name:  GP Read Only — GP Bookings Field Support
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-read-only/
 * Description:  Automatically make GP Bookings Resource and Booking Time fields read-only when GPPA populates a value.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
class GPRO_GP_Bookings_Fields {

	private $field_types = array( 'gpb_resource', 'gpb_booking_time' );

	public function __construct() {
		// Priority 12 so we run after GP Read Only's own `read_only_input()` (priority 11), which regenerates
		// the field markup from scratch and would otherwise discard our changes.
		add_filter( 'gform_field_input', array( $this, 'read_only_input' ), 12, 5 );
	}

	/**
	 * @param string   $input_html
	 * @param GF_Field $field
	 * @param mixed    $value
	 * @param int      $entry_id
	 * @param int      $form_id
	 *
	 * @return string
	 */
	public function read_only_input( $input_html, $field, $value, $entry_id, $form_id ) {

		if ( ! in_array( $field->get_input_type(), $this->field_types, true ) ) {
			return $input_html;
		}

		if ( GFCommon::is_form_editor() || GFCommon::is_entry_detail() ) {
			return $input_html;
		}

		$populated_value = $this->get_populated_value( $field, $value );

		if ( ! $this->is_dynamically_populated( $field ) || rgblank( $populated_value ) ) {
			return $input_html;
		}

		switch ( $field->get_input_type() ) {
			case 'gpb_resource':
				return $this->read_only_resource( $input_html, $field, $populated_value );
			case 'gpb_booking_time':
				return $this->read_only_booking_time( $input_html );
		}

		return $input_html;
	}

	/**
	 * Whether the field gets its value from GPPA, either via dynamic population or a Live Merge Tag in its
	 * default value.
	 *
	 * @param GF_Field $field
	 *
	 * @return bool
	 */
	public function is_dynamically_populated( $field ) {

		if ( ! function_exists( 'gp_populate_anything' ) ) {
			return false;
		}

		$gppa = gp_populate_anything();

		if ( $gppa->is_field_dynamically_populated( $field, 'values' ) ) {
			return true;
		}

		if ( ! $gppa->live_merge_tags ) {
			return false;
		}

		$default_values = is_array( $field->defaultValue ) ? $field->defaultValue : array( $field->defaultValue );

		foreach ( $default_values as $default_value ) {
			if ( is_string( $default_value ) && $gppa->live_merge_tags->has_live_merge_tag( $default_value ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the value that was populated for this field.
	 *
	 * GPPA stores the hydrated value on the field on every render, including the AJAX re-renders that
	 * follow a change to one of the field's filter values. Live Merge Tags are resolved into the rendered
	 * value instead, so that is used as the fallback.
	 *
	 * @param GF_Field $field
	 * @param mixed    $value The value passed to gform_field_input.
	 *
	 * @return mixed
	 */
	public function get_populated_value( $field, $value ) {

		$populated_value = rgar( $field, 'gppa_hydrated_value' );

		if ( rgblank( $populated_value ) ) {
			$populated_value = $value;
		}

		if ( is_array( $populated_value ) ) {
			$populated_value = implode( '', array_filter( $populated_value ) );
		}

		return $populated_value;
	}

	/**
	 * Disable the Resource drop down and submit its value via a hidden input.
	 *
	 * The drop down keeps its options and its selection so the populated resource is still displayed. A
	 * disabled <select> is not submitted, so the hidden input is what actually carries the value. The
	 * select also keeps its ID, since GP Bookings' JavaScript reads the selected resource from it when
	 * loading availability for the Booking Time field.
	 *
	 * @param string   $input_html
	 * @param GF_Field $field
	 * @param mixed    $value
	 *
	 * @return string
	 */
	public function read_only_resource( $input_html, $field, $value ) {

		// Automatic and static allocation modes already render a hidden input rather than a drop down.
		if ( strpos( $input_html, '<select' ) === false ) {
			return $input_html;
		}

		// Remove the name attribute so the disabled select isn't confused for the field input, then disable it.
		$input_html = preg_replace( '/(<select\b[^>]*?)\s+name=([\'"]).*?\2/', '$1', $input_html, 1 );
		$input_html = preg_replace(
			'/<select\b/',
			'<select disabled="disabled" aria-disabled="true" tabindex="-1" class="gf-default-disabled"',
			$input_html,
			1
		);

		$hidden_input = sprintf(
			'<input type="hidden" name="input_%d" value="%s" />',
			$field->id,
			esc_attr( $value )
		);

		$position = strrpos( $input_html, '</div>' );

		return $position === false ? $input_html . $hidden_input : substr_replace( $input_html, $hidden_input, $position, 0 );
	}

	/**
	 * Make the Booking Time picker non-interactive while leaving it on the page.
	 *
	 * The picker is left in place rather than replaced with a static value for two reasons: GP Bookings
	 * mounts its React app into this element on load and again whenever GPPA re-renders the field, and
	 * leaving it rendered means the user still sees the calendar (or slot list) with their booking time
	 * selected. `inert` blocks both pointer and keyboard interaction with everything inside it.
	 *
	 * The field's hidden input already carries the value and is left untouched.
	 *
	 * @param string $input_html
	 *
	 * @return string
	 */
	public function read_only_booking_time( $input_html ) {

		return str_replace(
			'<div class="gpb-booking-time-picker',
			'<div inert aria-disabled="true" class="gpb-booking-time-picker',
			$input_html
		);
	}

}

new GPRO_GP_Bookings_Fields();
