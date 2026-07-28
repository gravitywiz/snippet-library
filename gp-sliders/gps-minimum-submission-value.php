<?php
/**
 * Gravity Perks // Sliders // Set a Minimum Submission Value
 * https://gravitywiz.com/documentation/gravity-forms-sliders/
 *
 * Require a numeric Slider field's submitted value to meet a minimum without
 * changing the visual minimum of the slider track.
 *
 * For example, a slider can visually run from 0% to 7% while rejecting values
 * below 0.10%. Because the slider defaults to 0%, this also prevents an untouched
 * slider from being submitted.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of the snippet.
 */
class GPS_Minimum_Submission_Value {

	private $_args;

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'            => false,
			'slider_field_id'    => false,
			'minimum_value'      => false,
			'validation_message' => '',
		) );

		add_filter(
			sprintf(
				'gform_field_validation_%d_%d',
				$this->_args['form_id'],
				$this->_args['slider_field_id']
			),
			array( $this, 'validate' ),
			10,
			4
		);

	}

	public function validate( $result, $value, $form, $field ) {

		if ( ! $result['is_valid'] || ! class_exists( 'GP_Sliders' ) || ! GP_Sliders::is_slider_field( $field ) ) {
			return $result;
		}

		if ( rgar( $field, 'sliderMode' ) === 'choices' || ! is_numeric( $this->_args['minimum_value'] ) ) {
			return $result;
		}

		$submitted_value = is_array( $value )
			? rgar( $value, $field->id . '.1' )
			: $value;

		if ( rgblank( $submitted_value ) ) {
			return $result;
		}

		$number_format = rgar( $field, 'numberFormat' );
		$number_format = in_array( $number_format, array( 'currency', 'decimal_comma', 'decimal_dot' ), true )
			? $number_format
			: 'decimal_dot';

		$numeric_value = GFCommon::clean_number( $submitted_value, $number_format );

		if ( ! is_numeric( $numeric_value ) ) {
			return $result;
		}

		if ( (float) $numeric_value < (float) $this->_args['minimum_value'] ) {
			$result['is_valid'] = false;
			$result['message']  = $this->_args['validation_message']
				? $this->_args['validation_message']
				: sprintf(
					'Please select a value greater than or equal to %s.',
					$this->_args['minimum_value']
				);
		}

		return $result;
	}

}

# Configuration

new GPS_Minimum_Submission_Value( array(
	'form_id'            => 123,
	'slider_field_id'    => 4,
	'minimum_value'      => 0.10,
	'validation_message' => 'Please select an interest rate of at least 0.10%.',
) );
