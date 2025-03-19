<?php
/**
 * Gravity Wiz // Gravity Forms // Require Minimum and Maximum Character Limit for Gravity Forms
 * https://gravitywiz.com/require-minimum-character-limit-gravity-forms/
 *
 * Adds support for requiring a minimum and maximum number of characters for text-based Gravity Form fields.
 *
 * Plugin Name:  Gravity Forms - Require Minimum and Maximum Character Limit
 * Plugin URI:   https://gravitywiz.com/require-minimum-character-limit-gravity-forms/
 * Description:  Adds support for requiring a minimum and maximum number of characters for text-based Gravity Form fields.
 * Author:       Gravity Wiz
 * Version:      1.3.1
 * Author URI:   https://gravitywiz.com/
 */
class GW_Minimum_Characters {

	private $_args = array();

	public function __construct( $args = array() ) {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.7', '>=' ) ) {
			return;
		}

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'                => false,
			'field_id'               => false,
			'min_chars'              => 0,
			'max_chars'              => false,
			'validation_message'     => false,
			// translators: placeholder is a number
			'min_validation_message' => __( 'Please enter at least %s characters.' ),
			// translators: placeholder is a number
			'max_validation_message' => __( 'You may only enter %s characters.' ),
		) );

		/**
		 * @var int $form_id
		 * @var int $field_id
		 * @var int $min_chars
		 * @var false|int $max_chars
		 * @var false|string $validation_message
		 * @var string $min_validation_message
		 * @var string $max_validation_message
		 */
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $this->_args );

		if ( ! $form_id || ! $field_id || ( ! $min_chars && ! $max_chars ) ) {
			return;
		}

		if ( count( explode( '.', $field_id ) ) > 1 ) {
			$field_id = explode( '.', $field_id )[0];
		}

		// time for hooks
		add_filter( "gform_field_validation_{$form_id}_{$field_id}", array( $this, 'validate_character_count' ), 10, 4 );

	}

	public function validate_character_count( $result, $value, $form, $field ) {

		if ( is_array( $value ) ) {
			if ( count( explode( '.', $this->_args['field_id'] ) ) > 1 ) {
				$modifier = explode( '.', $this->_args['field_id'] )[1];
				$value    = rgar( $value, $field->id . '.' . $modifier );
			} elseif ( $field->type === 'list' ) {
				foreach ( $value as $row_values ) {
					// Handle single-column list fields
					if ( ! is_array( $row_values ) ) {
						$row_values = array( $row_values );
					}

					foreach ( $row_values as $row_value ) {
						$result = $this->validate_character_count( $result, $row_value, $form, $field );
					}
				}

				return $result;
			} else {
				return $result;
			}
		}

		// If value is empty, don't do validation.
		if ( empty( $value ) ) {
			return $result;
		}

		$char_count      = mb_strlen( $value );
		$is_min_reached  = $this->_args['min_chars'] !== false && $char_count >= $this->_args['min_chars'];
		$is_max_exceeded = $this->_args['max_chars'] !== false && $char_count > $this->_args['max_chars'];

		if ( ! $is_min_reached ) {

			$message = $this->_args['validation_message'];
			if ( ! $message ) {
				$message = $this->_args['min_validation_message'];
			}

			$result['is_valid'] = false;
			$result['message']  = sprintf( $message, $this->_args['min_chars'] );

		} elseif ( $is_max_exceeded ) {

			$message = $this->_args['max_validation_message'];
			if ( ! $message ) {
				$message = $this->_args['validation_message'];
			}

			$result['is_valid'] = false;
			$result['message']  = sprintf( $message, $this->_args['max_chars'] );

		}

		return $result;
	}

}

# Configuration

new GW_Minimum_Characters( array(
	'form_id'                => 123,
	'field_id'               => 4,
	'min_chars'              => 3,
	'max_chars'              => 5,
	// translators: placeholder is a number
	'min_validation_message' => __( 'Oops! You need to enter at least %s characters.' ),
	// translators: placeholder is a number
	'max_validation_message' => __( 'Oops! You can only enter %s characters.' ),
) );
