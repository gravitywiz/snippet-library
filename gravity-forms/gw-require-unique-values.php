<?php
/**
 * Gravity Wiz // Gravity Forms // Require Unique Values Between Fields
 * https://gravitywiz.com/gravity-forms-require-unique-values-for-different-fields/
 *
 * Allows you to require two or more fields on the same form to be different from each other. For example, if you are
 * collecting a personal phone number and an emergency contact phone number, this functionality can be used to ensure
 * that the same number is not used for both fields.
 *
 * Plugin Name:  Gravity Forms - Require Unique Values Between Fields
 * Plugin URI:   https://gravitywiz.com/gravity-forms-require-unique-values-for-different-fields/
 * Description:  Require two or more fields on the same form to be different from each other.
 * Author:       Gravity Wiz
 * Version:      0.3
 * Author URI:   https://gravitywiz.com/
 */
class GW_Require_Unique_Values {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'             => false,
			'field_ids'           => false,
			'validation_message'  => __( 'Please enter a unique value.' ),
			'validate_all_fields' => false,
			'mode'                => 'collective', // 'collective' or 'individual' (experimental)
			'case_sensitive'      => false,
		) );

		// No validation when the field id list is empty.
		if ( empty( $this->_args['field_ids'] ) ) {
			return;
		}

		$this->_args['master_field_id'] = array_shift( $this->_args['field_ids'] );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.9', '>=' ) ) {
			return;
		}

		add_filter( sprintf( 'gform_field_validation_%s', $this->_args['form_id'] ), array( $this, 'validate' ), 10, 4 );
	}

	public function validate( $result, $value, $form, $field ) {

		if ( ! $this->is_applicable_field( $field ) ) {
			return $result;
		}

		if ( $field->get_input_type() == 'list' ) {
			return $this->validate_list( $result, $value );
		}

		$value = $this->get_filtered_value( $field );
		if ( empty( $value ) ) {
			return $result;
		}

		if ( $this->_args['mode'] === 'individual' ) {

			$groups    = $this->get_group_values( $form, $field->id, false );
			$is_unique = true;

			foreach ( $value as $target ) {
				foreach ( $groups as $group ) {
					if ( in_array( $target, $group ) ) {
						$is_unique = false;
						break 2;
					}
				}
			}
		} else {
			$values = $this->get_group_values( $form, $field->id );

			// Check if this should be validated as whole field
			$all_field_ids = array_merge( $this->_args['field_ids'], array( $this->_args['master_field_id'] ) );
			$validate_as_whole = in_array( $field->id, $all_field_ids );

			// If the field has inputs, let's loop through them and check if they are unique.
			if ( is_array( $field->inputs ) && ! empty( $field->inputs ) && ! $validate_as_whole ) {
				$is_unique = true;

				foreach ( $field->inputs as $input ) {
					$input_id    = $input['id'];
					$input_value = rgars( $value, $input_id );

					if ( empty( $input_value ) ) {
						continue;
					}

					// Ensure that this input is to be validated.
					if ( ! in_array( $input_id, array_keys( $values ) ) ) {
						continue;
					}

					$input_hash = $this->get_value_hash( array( $input_value ) );
					$is_unique  = ! in_array( $input_hash, $values );
					if ( ! $is_unique ) {
						break;
					}
				}
			} else {
				$is_unique = ! in_array( $this->get_value_hash( $value ), $values );
			}
		}

		if ( $result['is_valid'] && ! $is_unique ) {
			$result['is_valid'] = false;
			$result['message']  = $this->_args['validation_message'];
		}

		return $result;
	}

	public function validate_list( $result, $value ) {
		$rows      = count( $value );
		$is_unique = true;

		// Multi-Column List
		if ( is_array( rgar( $value, 0 ) ) ) {
			foreach ( array_keys( $value[0] ) as $column_name ) {
				$column_values = wp_list_pluck( $value, $column_name );
				// Count unique values in each column.
				if ( $rows != count( array_unique( $column_values ) ) ) {
					$is_unique = false;
				}
			}
		} else {
			// Count unique values in the Single Column List.
			if ( $rows != count( array_unique( $value ) ) ) {
				$is_unique = false;
			}
		}

		if ( $result['is_valid'] && ! $is_unique ) {
			$result['is_valid'] = false;
			$result['message']  = $this->_args['validation_message'];
		}

		return $result;
	}

	public function get_group_values( $form, $exclude_field_id, $do_hash = true ) {

		$field_ids   = $this->_args['field_ids'];
		$field_ids[] = $this->_args['master_field_id'];

		$values = array();

		foreach ( $field_ids as $field_id ) {

			if ( $field_id == $exclude_field_id ) {
				continue;
			}

			$field    = GFFormsModel::get_field( $form, $field_id );
			$input_id = preg_match( '/^\d+\.\w+$/', $field_id ) ? (string) $field_id : null;
			$value    = $this->get_filtered_value( $field, $input_id );

			if ( ! empty( $value ) ) {
				$values[ (string) $field_id ] = $do_hash ? $this->get_value_hash( $value ) : $value;
			}
		}

		return $values;
	}

	/**
	 * @param GF_Field $field
	 *
	 * @return array
	 */
	public function get_filtered_value( $field, $input_id = null ) {

		if ( $field->get_input_type() == 'fileupload' && ! $field->multipleFiles ) {
			/** @var GF_Field_FileUpload $field */
			$value = basename( rgars( $_FILES, sprintf( 'input_%d/name', $field->id ) ) );
		} else {
			$value = $field->get_value_submission( array() );
			// Product values are stored as Value|Price, we just need to compare Value.
			if ( rgar( $field, 'enablePrice' ) ) {
				$value = GFCommon::get_selection_value( $value );
			}
		}

		if ( $input_id && is_array( $value ) && isset( $value[ $input_id ] ) ) {
			$value = $value[ $input_id ];
		}

		// When using a field ID (not input ID) for multi-input fields, combine all subfield values into one for validation.
		if ( ! $input_id && is_array( $field->inputs ) && is_array( $value ) ) {
			$all_field_ids = array_merge( $this->_args['field_ids'], array( $this->_args['master_field_id'] ) );
			if ( in_array( $field->id, $all_field_ids ) ) {
				$combined_parts = array();
				foreach ( $field->inputs as $input ) {
					$input_value = rgar( $value, $input['id'] );
					if ( ! empty( $input_value ) ) {
						$combined_parts[] = $input_value;
					}
				}
				$value = array( implode( ' ', $combined_parts ) );
			}
		}

		$value = ! is_array( $value ) ? array( $value ) : $value;
		$value = array_filter( $value );

		return $value;
	}

	public function get_value_hash( $value ) {

		if ( ! $this->_args['case_sensitive'] ) {
			$value = array_map( 'strtolower', $value );
		}

		// Replace values like "1.1" with "x.1" to make it generic for comparison.
		if ( is_array( $value ) ) {
			$old = $value;
			foreach ( $old as $key => $_value ) {
				$ids = explode( '.', $key );
				if ( count( $ids ) > 1 ) {
					$ids[0] = 'x';
				}
				unset( $value[ $key ] );
				$value[ implode( '.', $ids ) ] = $_value;
			}
		}

		return json_encode( $value );
	}

	public function is_applicable_field( $field ) {

		// A single list field can be used for validation (validation logic to use unique values check over columns).
		if ( $field->id == $this->_args['master_field_id'] && ! $this->_args['field_ids'] && $field->get_input_type() == 'list' ) {
			return true;
		} elseif ( ! $this->_args['field_ids'] ) {
			return false;
		} elseif ( $this->_args['validate_all_fields'] && $field->id == $this->_args['master_field_id'] ) {
			return true;
		} elseif ( ! in_array( $field->id, array_map( 'absint', array_merge( $this->_args['field_ids'], array( $this->_args['master_field_id'] ) ) ) ) ) {
			return false;
		}

		return true;
	}
}

# Configuration

new GW_Require_Unique_Values( array(
	'form_id'   => 5,
	'field_ids' => array( 1, 3.3 ),
) );
