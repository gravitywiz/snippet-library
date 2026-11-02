<?php
/**
 * Gravity Perks // Read Only // GP Bookings Field Support
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 *
 * GP Read Only has no handling for the GP Bookings Resource and Booking Time fields. This snippet adds that support.
 *
 * Usage:
 *
 * 1. Install this snippet as a plugin (or via a snippet plugin).
 * 2. Enable the "Read-only" field setting on your GP Bookings Resource and/or Booking Time field.
 * 3. Configure GPPA to populate the field's value.
 *
 * Plugin Name:  GP Read Only — GP Bookings Field Support
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-read-only/
 * Description:  Apply GP Read Only to GP Bookings Resource and Booking Time fields when GPPA populates a value.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com/
 */
class GPRO_GP_Bookings_Fields {

	/**
	 * Field types handled by this snippet.
	 */
	private $field_types = array( 'gpb_resource', 'gpb_booking_time' );

	public function __construct() {
		// Priority 12 so we run after GP Read Only's own `read_only_input()` (priority 11), which regenerates
		// the field markup from scratch and would otherwise discard our changes.
		add_filter( 'gform_field_input', array( $this, 'read_only_input' ), 12, 5 );
		add_filter( 'gform_pre_render', array( $this, 'maybe_flag_form' ) );
		add_action( 'wp_footer', array( $this, 'styles' ) );
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

		/**
		 * Filter whether a GP Bookings field should be rendered as read-only.
		 *
		 * @param bool     $is_read_only    Whether the field should be read-only.
		 * @param GF_Field $field           The current field.
		 * @param mixed    $populated_value The value GPPA populated, if any.
		 */
		$is_read_only = gf_apply_filters(
			array( 'gpro_gpb_is_read_only', $form_id, $field->id ),
			rgar( $field, 'gwreadonly_enable' ) && ! rgblank( $populated_value ),
			$field,
			$populated_value
		);

		if ( ! $is_read_only ) {
			return $input_html;
		}

		switch ( $field->get_input_type() ) {
			case 'gpb_resource':
				return $this->read_only_resource( $input_html, $field, $populated_value, $form_id );
			case 'gpb_booking_time':
				return $this->read_only_booking_time( $input_html, $field, $populated_value );
		}

		return $input_html;
	}

	/**
	 * Flag forms that have a read-only GP Bookings field so our styles are printed.
	 *
	 * This runs on render rather than when a field is actually made read-only so that the styles are
	 * present even when GPPA only returns a value later, via one of its AJAX re-renders.
	 *
	 * @param array $form
	 *
	 * @return array
	 */
	public function maybe_flag_form( $form ) {

		foreach ( $form['fields'] as $field ) {
			if ( in_array( $field->get_input_type(), $this->field_types, true ) && rgar( $field, 'gwreadonly_enable' ) ) {
				self::$has_read_only_field = true;
				break;
			}
		}

		return $form;
	}

	/**
	 * Get the value GPPA populated for this field.
	 *
	 * GPPA stores the hydrated value on the field for exactly this purpose; it is set on every render,
	 * including the AJAX re-renders that follow a change to one of the field's filter values.
	 *
	 * @param GF_Field $field
	 * @param mixed    $value The value passed to gform_field_input.
	 *
	 * @return mixed
	 */
	public function get_populated_value( $field, $value ) {

		if ( ! rgar( $field, 'gppa-values-enabled' ) ) {
			return null;
		}

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
	 * A disabled <select> is not submitted, so the hidden input is what actually carries the value. The
	 * select keeps its ID since GP Bookings' JavaScript reads the selected resource from it when loading
	 * availability for the Booking Time field.
	 *
	 * @param string   $input_html
	 * @param GF_Field $field
	 * @param mixed    $value
	 * @param int      $form_id
	 *
	 * @return string
	 */
	public function read_only_resource( $input_html, $field, $value, $form_id ) {

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

		return $this->append_to_container( $input_html, $hidden_input );
	}

	/**
	 * Hide the Booking Time picker and display the populated booking time as a disabled text input.
	 *
	 * The field's own hidden input already carries the value, so it is left untouched. The picker element
	 * is hidden rather than removed; GP Bookings mounts its React app into it on load and again whenever
	 * GPPA re-renders the field, and logs an error if it is missing.
	 *
	 * @param string   $input_html
	 * @param GF_Field $field
	 * @param mixed    $value
	 *
	 * @return string
	 */
	public function read_only_booking_time( $input_html, $field, $value ) {

		$input_html = str_replace(
			'class="gpb-booking-time-picker',
			'class="gpro-gpb-hidden-picker gpb-booking-time-picker',
			$input_html
		);

		$display = sprintf(
			'<input type="text" class="gpro-gpb-readonly-display gf-default-disabled %s" value="%s" disabled="disabled" aria-disabled="true" tabindex="-1" />',
			esc_attr( $field->size ),
			esc_attr( $this->format_booking_time( $field, $value ) )
		);

		return $this->append_to_container( $input_html, $display );
	}

	/**
	 * Format a Booking Time value for display using GP Bookings' own formatting.
	 *
	 * @param GF_Field $field
	 * @param mixed    $value
	 *
	 * @return string
	 */
	public function format_booking_time( $field, $value ) {

		if ( ! is_callable( array( $field, 'get_value_entry_detail' ) ) ) {
			return $value;
		}

		try {
			$formatted = $field->get_value_entry_detail( $value, array(), false, 'text' );
		} catch ( \Throwable $e ) {
			return $value;
		}

		return rgblank( $formatted ) ? $value : $formatted;
	}

	/**
	 * Append markup inside the field's ginput_container, falling back to the end of the markup.
	 *
	 * @param string $input_html
	 * @param string $markup
	 *
	 * @return string
	 */
	public function append_to_container( $input_html, $markup ) {

		$position = strrpos( $input_html, '</div>' );
		if ( $position === false ) {
			return $input_html . $markup;
		}

		return substr_replace( $input_html, $markup, $position, 0 );
	}

	private static $has_read_only_field = false;

	public function styles() {

		if ( ! self::$has_read_only_field ) {
			return;
		}

		?>
		<style>
			.gpro-gpb-hidden-picker {
				display: none !important;
			}
			.gpro-gpb-readonly-display {
				width: 100%;
			}
		</style>
		<?php
	}

}

new GPRO_GP_Bookings_Fields();
