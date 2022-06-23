<?php
/**
 * Require All Columns of List Field
 * http://gravitywiz.com/require-all-columns-of-list-field/
 */
class GWRequireListColumns {

	private $field_ids;

	public static $fields_with_req_cols = array();

	function __construct( $form_id = '', $field_ids = array(), $required_cols = array() ) {

		$this->field_ids     = ! is_array( $field_ids ) ? array( $field_ids ) : $field_ids;
		$this->required_cols = ! is_array( $required_cols ) ? array( $required_cols ) : $required_cols;

		if ( ! empty( $this->required_cols ) ) {

			// convert values from 1-based index to 0-based index, allows users to enter "1" for column "0"
			$this->required_cols = array_map( function( $value ) {
				return $value - 1;
			}, $this->required_cols );

			if ( ! isset( self::$fields_with_req_cols[ $form_id ] ) ) {
				self::$fields_with_req_cols[ $form_id ] = array();
			}

			// keep track of which forms/fields have special require columns so we can still apply GWRequireListColumns
			// to all list fields and then override with require columns for specific fields as well
			self::$fields_with_req_cols[ $form_id ] = array_merge( self::$fields_with_req_cols[ $form_id ], $this->field_ids );

		}

		$form_filter = $form_id ? "_{$form_id}" : $form_id;
		add_filter( "gform_validation{$form_filter}", array( &$this, 'require_list_columns' ) );

	}

	function require_list_columns( $validation_result ) {

		$form                 = $validation_result['form'];
		$new_validation_error = false;

		foreach ( $form['fields'] as &$field ) {

			if ( ! $this->is_applicable_field( $field, $form ) ) {
				continue;
			}

			/**
			 * @var \GF_Field_List $field
			 */
			$values = rgpost( "input_{$field['id']}" );

			// If we got specific fields, loop through those only
			if ( count( $this->required_cols ) ) {

				$rows = $field->create_list_array_recursive( $field->get_value_submission( array() ) );

				foreach ( $rows as $row ) {
					foreach ( $this->required_cols as $required_col ) {
						$row = array_values( $row );
						if ( rgblank( $row[ $required_col ] ) ) {
							$new_validation_error        = true;
							$field['failed_validation']  = true;
							$field['validation_message'] = $field['errorMessage'] ? $field['errorMessage'] : 'All required inputs must be filled out.';
						}
					}
				}
			} else {

				// skip fields that have req cols specified by another GWRequireListColumns instance
				$fields_with_req_cols = rgar( self::$fields_with_req_cols, $form['id'] );
				if ( is_array( $fields_with_req_cols ) && in_array( $field['id'], $fields_with_req_cols ) ) {
					continue;
				}

				foreach ( $values as $value ) {
					if ( empty( $value ) && $value !== '0' ) {
						$new_validation_error        = true;
						$field['failed_validation']  = true;
						$field['validation_message'] = $field['errorMessage'] ? $field['errorMessage'] : 'All inputs must be filled out.';
					}
				}
			}
		}

		$validation_result['form']     = $form;
		$validation_result['is_valid'] = $new_validation_error ? false : $validation_result['is_valid'];

		return $validation_result;
	}

	function is_applicable_field( $field, $form ) {

		if ( $field['pageNumber'] != GFFormDisplay::get_source_page( $form['id'] ) ) {
			return false;
		}

		if ( GFFormsModel::get_input_type( $field ) != 'list' || RGFormsModel::is_field_hidden( $form, $field, array() ) ) {
			return false;
		}

		// if the field has already failed validation, we don't need to fail it again
		if ( ! $field['isRequired'] || $field['failed_validation'] ) {
			return false;
		}

		if ( empty( $this->field_ids ) ) {
			return true;
		}

		return in_array( $field['id'], $this->field_ids );
	}

}

// Uncomment one of the following lines and customize to activate this snippet.
// Accepted parameters
// new GWRequireListColumns($form_id, $field_ids);

// apply to all list fields on all forms
// new GWRequireListColumns();

// apply to all list fields on a specific form
// new GWRequireListColumns(4);

// apply to specific list field on a specific form
// new GWRequireListColumns(4, 2);

// apply to specific list fields (plural) on a specific form
// new GWRequireListColumns(4, array(2,3));

// require specific field columns on a specific form
// new GWRequireListColumns( 240, 1, array( 2, 3 ) );
