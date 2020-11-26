<?php

/**
 * Gravity Wiz // Gravity Forms // Random Fields
 *
 * Randomly display a specified number of fields on your form.
 *
 * @version 1.1
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    https://gravitywiz.com/random-fields-with-gravity-forms/
 *
 * Plugin Name: Gravity Forms Random Fields
 * Plugin URI:  https://gravitywiz.com/random-fields-with-gravity-forms/
 * Description: Randomly display a specified number of fields on your form.
 * Author:      Gravity Wiz
 * Version:     1.1
 * Author URI:  http://gravitywiz.com
 */
class GFRandomFields {

	public $all_random_field_ids;
	public $display_count;
	public $selected_field_ids = array();

	public function __construct( $form_id, $display_count = 5, $random_field_ids = false ) {

		$this->_form_id             = $form_id;
		$this->all_random_field_ids = (array) $random_field_ids;
		$this->display_count        = $display_count;

		add_filter( "gform_pre_render_$form_id", array( $this, 'pre_render' ) );
		add_filter( "gform_form_tag_$form_id", array( $this, 'store_selected_field_ids' ) );
		add_filter( "gform_validation_$form_id", array( $this, 'validate' ) );
		add_filter( "gform_pre_submission_filter_$form_id", array( $this, 'pre_render' ) );

		//add_filter( "gform_admin_pre_render_$form_id",      array( $this, 'admin_pre_render' ) );
		//add_action( 'gform_entry_created',                  array( $this, 'save_selected_field_ids_meta' ), 10, 2 );

	}

	public function pre_render( $form ) {
		return $this->filter_form_fields( $form, $this->get_selected_field_ids() );
	}

	public function admin_pre_render( $form ) {

		if ( $form['id'] != $this->_form_id ) {
			return $form;
		}

		return $form;
	}

	public function store_selected_field_ids( $form_tag ) {
		$hash  = $this->get_selected_field_ids_hash();
		$value = implode( ',', $this->get_selected_field_ids() );
		$input = sprintf( '<input type="hidden" value="%s" name="gfrf_field_ids_%s">', $value, $hash );
		return $form_tag . $input;
	}

	public function validate( $validation_result ) {

		$validation_result['form']     = $this->filter_form_fields( $validation_result['form'], $this->get_selected_field_ids() );
		$validation_result['is_valid'] = true;

		foreach ( $validation_result['form']['fields'] as $field ) {
			if ( $field['failed_validation'] ) {
				$validation_result['is_valid'] = false;
			}
		}

		return $validation_result;
	}

	public function filter_form_fields( $form, $selected_fields ) {

		$filtered_fields = array();

		foreach ( $form['fields'] as $field ) {

			if ( in_array( $field['id'], $this->all_random_field_ids ) ) {
				if ( in_array( $field['id'], $selected_fields ) ) {
					$filtered_fields[] = $field;
				}
			} else {
				$filtered_fields[] = $field;
			}
		}

		$form['fields'] = $filtered_fields;

		return $form;
	}

	public function get_selected_field_ids( $entry_id = false ) {

		// check if class has already init fields
		if ( ! empty( $this->selected_field_ids ) ) {
			return $this->selected_field_ids;
		}

		$hash = $this->get_selected_field_ids_hash();

		// If entry ID is passed, retrieve selected fields IDs from entry meta.
		if ( $entry_id ) {
			$field_ids = gform_get_meta( $entry_id, "_gfrf_field_ids_{$hash}" );

			return is_array( $field_ids ) ? $field_ids : array();
		}

		// Check if fields have been submitted.
		$field_ids = rgpost( "gfrf_field_ids_{$hash}" );
		if ( ! empty( $field_ids ) ) {
			return explode( ',', $field_ids );
		}

		$field_ids = array();
		$keys      = array_rand( $this->all_random_field_ids, $this->display_count );

		if ( ! is_array( $keys ) ) {
			$keys = array( $keys );
		}

		foreach ( $keys as $key ) {
			$field_ids[] = $this->all_random_field_ids[ $key ];
		}

		$this->selected_field_ids = $field_ids;

		return $field_ids;
	}

	public function get_selected_field_ids_hash() {
		return wp_hash( implode( '_', $this->all_random_field_ids ) );
	}

	public function save_selected_field_ids_meta( $entry, $form ) {
		if ( $form['id'] == $this->_form_id ) {
			$hash = $this->get_selected_field_ids_hash();
			gform_add_meta( $entry['id'], "_gfrf_field_ids_{$hash}", $this->get_selected_field_ids() );
		}
	}

}
