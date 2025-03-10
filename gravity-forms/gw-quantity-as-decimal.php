<?php
/**
 * Gravity Wiz // Gravity Forms // Accept Decimal Values for Quantity Fields
 * https://gravitywiz.com/enable-decimal-values-in-quantity-fields/
 *
 * Allows you to accept decimal values in Quantity fields, converting any Quantity field into a weight field.
 *
 * Plugin Name:  Gravity Forms - Accept Decimal Values for Quantity Fields
 * Plugin URI:   https://gravitywiz.com/enable-decimal-values-in-quantity-fields/
 * Description:  Allows you to accept decimal values in Quantity fields.
 * Author:       Gravity Wiz
 * Version:      1.3
 * Author URI:   https://gravitywiz.com/
 */
class GW_Quantity_Decimal {

	private static $_current_form;

	function __construct( $form_id, $field_ids = array(), $global = false ) {

		if ( ! is_array( $field_ids ) ) {
			$field_ids = array( $field_ids );
		}

		$this->form_id   = ( ! $global ) ? $form_id : null;
		$this->field_ids = $field_ids;
		$this->global    = $global;

		add_action( 'init', array( $this, 'init' ) );

	}

	/**
	 * Initializes the plugin by adding the necessary Gravity Forms filters.
	 *
	 * This method first verifies that Gravity Forms is loaded. It then conditionally registers
	 * a field validation filter for decimal quantities on either a global or a form-specific basis.
	 * For Gravity Forms versions before 2.8 with HTML5 disabled, only the validation filter is added.
	 * Otherwise, additional filters are registered to stash the current form, modify the quantity
	 * input tag to allow decimal values, and adjust input content to ensure the 'step' attribute is set to 'any'.
	 */
	function init() {

		// make sure Gravity Forms is loaded
		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		if ( $this->global ) {
			add_filter( 'gform_field_validation', array( $this, 'allow_quantity_float' ), 10, 4 );
		} else {
			add_filter( 'gform_field_validation_' . $this->form_id, array( $this, 'allow_quantity_float' ), 10, 4 );
		}

		// For GF versions before 2.8 and HTML5 disabled, ignore the rest.
		if ( version_compare( GFCommon::$version, '2.8', '<' ) && ! GFFormsModel::is_html5_enabled() ) {
			return;
		}

		// For GF versions 2.8 and beyond, HTML5 is enabled by default.
		// Also for GF versions prior to 2.8 having HTML5 manually enabled.
		add_filter( 'gform_pre_render', array( $this, 'stash_current_form' ) );
		add_filter( 'gform_field_input', array( $this, 'modify_quantity_input_tag' ), 10, 5 );

		add_filter( 'gform_field_content', array( $this, 'fix_content' ), 10, 5 );
	}

	/**
	 * Validates and allows decimal input for quantity and product fields.
	 *
	 * This callback checks if the field is enabled for decimals and if its user-submitted value is a valid decimal number
	 * (accepting both dot and comma as separators). If a valid decimal is detected, it updates the validation result accordingly.
	 *
	 * @param array  $result The current validation result array, which includes an 'is_valid' flag.
	 * @param mixed  $value  The submitted value for the field.
	 * @param object $form   The form object being processed.
	 * @param object $field  The field object including type and validation configuration.
	 *
	 * @return array The modified validation result.
	 */
	function allow_quantity_float( $result, $value, $form, $field ) {
		if (
			$this->is_enabled_field( $field ) &&
			in_array( $field->type, array( 'product', 'quantity' ) ) &&
			in_array( $field->validation_message, array( __( 'Please enter a valid quantity. Quantity cannot contain decimals.', 'gravityforms' ), __( 'Please enter a valid quantity', 'gravityforms' ) ) ) ) {
			$is_numeric_decimal_dot   = $field->type == 'product' ? GFCommon::is_numeric( rgpost( "input_{$field['id']}_3" ), 'decimal_dot' ) : GFCommon::is_numeric( rgpost( "input_{$field['id']}" ), 'decimal_dot' );
			$is_numeric_decimal_comma = $field->type == 'product' ? GFCommon::is_numeric( rgpost( "input_{$field['id']}_3" ), 'decimal_comma' ) : GFCommon::is_numeric( rgpost( "input_{$field['id']}" ), 'decimal_comma' );
			if ( $is_numeric_decimal_dot || $is_numeric_decimal_comma ) {
				$result['is_valid'] = true;
			}
		}
		return $result;
	}

	function stash_current_form( $form ) {
		self::$_current_form = $form;
		return $form;
	}

	/**
	 * Modifies the HTML markup for a Gravity Forms quantity field to enable decimal input.
	 *
	 * If the form ID matches the configured form (or if the plugin applies globally), the currently
	 * processed form is stored, and the field is enabled for decimals, this function updates the
	 * input field markup by adding a "step='any'" attribute to the number input element. Otherwise,
	 * it returns the unmodified markup.
	 *
	 * @param string $markup  The original HTML markup for the field input.
	 * @param array  $field   The configuration array for the form field.
	 * @param mixed  $value   The current value of the field.
	 * @param mixed  $lead_id The identifier for the current lead/entry.
	 * @param int    $form_id The identifier of the form being processed.
	 *
	 * @return string The modified HTML markup with an added "step='any'" attribute if applicable,
	 *                or the original markup if the conditions are not met.
	 */
	function modify_quantity_input_tag( $markup, $field, $value, $lead_id, $form_id ) {

		$is_correct_form         = $this->form_id == $form_id || $this->global;
		$is_correct_stashed_form = self::$_current_form && self::$_current_form['id'] == $form_id;

		if ( ! $is_correct_form || ! $is_correct_stashed_form || ! $this->is_enabled_field( $field ) ) {
			return $markup;
		}

		$markup = $this->get_field_input( $field, $value, self::$_current_form );

		$search  = 'type=\'number\'';
		$replace = $search . ' step=\'any\'';
		$markup  = str_replace( $search, $replace, $markup );

		return $markup;
	}

	/**
	 * Modifies HTML content for quantity fields to support decimal values.
	 *
	 * This function scans the provided HTML content for input elements with the "ginput_quantity" class.
	 * If an input field includes a decimal value and lacks a step attribute set to "any", the function
	 * updates the input tag to include step="any", ensuring that decimal quantities are handled correctly.
	 *
	 * @param string $content The HTML content containing the input fields.
	 * @param mixed $field Field data provided by Gravity Forms.
	 * @param mixed $value Current value of the field.
	 * @param int|string $lead_id The entry identifier.
	 * @param int|string $form_id The form identifier.
	 *
	 * @return string The modified HTML content with updated input tags.
	 */
	function fix_content( $content, $field, $value, $lead_id, $form_id ) {
		// ensure the step is 'any' for any fields that have a decimal value.
		return preg_replace_callback(
			'/<input([^>]*class=[\'"]ginput_quantity[\'"][^>]*)>/i',
			function ( $matches ) {
				$inputTag = $matches[0];

				// Check if the input has a decimal value, and if does not have 'any'.
				if ( preg_match('/\bvalue=[\'"]([\d]+\.[\d]+)[\'"]/i', $inputTag, $valueMatch ) ) {
					if ( ! preg_match('/\bstep=[\'"]any[\'"]/i', $inputTag ) ) {
						$inputTag = preg_replace( '/<input/i', '<input step="any"', $inputTag, 1 );
					}
				}

				return $inputTag;
			},
			$content
		);
	}

	/**
	 * Retrieves the field input markup while avoiding recursive modification.
	 *
	 * Temporarily disables the quantity input tag modification filter to prevent recursion,
	 * retrieves the field input HTML via GFCommon, and then re-applies the filter.
	 *
	 * @param array $field The configuration array for the Gravity Forms field.
	 * @param mixed $value The current value of the field.
	 * @param array $form The configuration array for the Gravity Forms form.
	 * @return string The generated HTML markup for the field input.
	 */
	function get_field_input( $field, $value, $form ) {

		remove_filter( 'gform_field_input', array( $this, 'modify_quantity_input_tag' ), 10, 5 );

		$input = GFCommon::get_field_input( $field, $value, 0, $form['id'], $form );

		add_filter( 'gform_field_input', array( $this, 'modify_quantity_input_tag' ), 10, 5 );

		return $input;
	}

	function is_enabled_field( $field ) {
		return is_array( $this->field_ids ) && ! empty( $this->field_ids ) ? in_array( $field['id'], $this->field_ids ) : true;
	}

}
// Global sub-class
class GW_Quantity_Decimal_Global extends GW_Quantity_Decimal {
	function __construct( $form_id = null, $field_ids = array() ) {
		parent::__construct( $form_id, $field_ids, true );
	}
}

# accept quantity as decimal for any fields
new GW_Quantity_Decimal( 123 );

# accept quantity as decimal for a single field
// new GW_Quantity_Decimal( 123, 1 );

# accept quantity as decimal for a group of fields
// new GW_Quantity_Decimal( 123, array( 1, 2, 3 ) );

# accept quantity as decimal for any fields in ALL forms
// new GW_Quantity_Decimal_Global();

# accept quantity as decimal for a single field in ALL forms (field ID must match globally)
// new GW_Quantity_Decimal_Global( null, 1 );

# accept quantity as decimal for a group of fields
// new GW_Quantity_Decimal_Global( null, array( 1, 2, 3 ) );
