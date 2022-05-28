<?php

/**
 * Gravity Wiz // Gravity Forms // Random Fields
 *
 * Randomly display a specified number of fields on your form.
 *
 * @version 1.3.x
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    https://gravitywiz.com/random-fields-with-gravity-forms/
 *
 * Plugin Name: Gravity Forms Random Fields
 * Plugin URI:  https://gravitywiz.com/random-fields-with-gravity-forms/
 * Description: Randomly display a specified number of fields on your form.
 * Author:      Gravity Wiz
 * Version:     1.3.x
 * Author URI:  http://gravitywiz.com
 */
class GFRandomFields {

	public $all_random_field_ids;
	public $display_count;
	public $selected_field_ids = array();
	public $preserve_order;
	public $restructure_pages;

	public function __construct( $form_id, $display_count = 5, $random_field_ids = false, $preserve_order = false, $restructure_pages = true ) {

		$this->_form_id             = (int) $form_id;
		$this->all_random_field_ids = array_map( 'intval', array_filter( (array) $random_field_ids ) );
		$this->display_count        = (int) $display_count;
		$this->preserve_order       = (bool) $preserve_order;
		$this->restructure_pages    = (bool) $restructure_pages;

		add_filter( "gform_pre_render_$form_id", array( $this, 'pre_render' ) );
		add_filter( "gform_pre_process_$form_id", array( $this, 'pre_render' ) );

		add_filter( "gform_form_tag_$form_id", array( $this, 'store_selected_field_ids' ), 10, 2 );

		add_action( 'gform_entry_created', array( $this, 'save_selected_field_ids_meta' ), 10, 2 );

	}

	public function pre_render( $form ) {
		return $this->filter_form_fields( $form, $this->get_selected_field_ids( false, $form ) );
	}

	public function store_selected_field_ids( $form_tag, $form ) {
		$hash  = $this->get_selected_field_ids_hash( $form );
		$value = implode( ',', $this->get_selected_field_ids( false, $form ) );
		$input = sprintf( '<input type="hidden" value="%s" name="gfrf_field_ids_%s">', $value, $hash );
		return $form_tag . $input;
	}

	public function validate( $validation_result ) {

		$validation_result['form']     = $this->filter_form_fields( $validation_result['form'], $this->get_selected_field_ids( false, $validation_result['form'] ) );
		$validation_result['is_valid'] = true;

		foreach ( $validation_result['form']['fields'] as $field ) {
			if ( $field['failed_validation'] ) {
				$validation_result['is_valid'] = false;
			}
		}

		return $validation_result;
	}

	public function filter_form_fields( $form, $selected_fields ) {

		// Prevent the form from being randomized multiple times. Once per runtime is enough. 😅
		if ( rgar( $form, 'gwrfRandomized' ) ) {
			return $form;
		}

		$filtered_fields = array();
		$random_indexes  = array();
		$selected_fields = array_map( 'intval', $selected_fields );

		foreach ( $form['fields'] as $field ) {

			if ( $field->type === 'page' ) {
				continue;
			}

			if ( in_array( (int) $field['id'], $this->get_random_field_ids( $form['fields'] ), true ) ) {
				if ( in_array( (int) $field['id'], $selected_fields, true ) ) {
					$filtered_fields[] = $field;
					$random_indexes[]  = count( $filtered_fields ) - 1;
				}
			} else {
				$filtered_fields[] = $field;
			}
		}

		if ( $this->restructure_pages ) {
			$filtered_fields = $this->handle_pagination( $filtered_fields, $form );
		}

		if ( ! $this->preserve_order ) {

			$reordered_fields = array();
			$random_index_key = $random_indexes;

			shuffle( $random_indexes );

			foreach ( $filtered_fields as $index => $field ) {
				if ( in_array( $index, $random_index_key, true ) ) {
					$random_index       = array_pop( $random_indexes );
					$reordered_fields[] = $filtered_fields[ $random_index ];
				} else {
					$reordered_fields[] = $filtered_fields[ $index ];
				}
			}

			$filtered_fields = $reordered_fields;

		}

		$form['gwrfRandomized'] = true;
		$form['fields']         = $filtered_fields;

		return $form;
	}

	public function handle_pagination( $filtered_fields, $form ) {

		$pages = array();
		foreach ( $form['fields'] as $field ) {
			if ( $field->type === 'page' ) {
				$pages[] = $field;
			}
		}

		// Get all the page numbers that exist now that the fields have been filtered.
		$page_numbers = array_values( array_unique( wp_list_pluck( $filtered_fields, 'pageNumber' ) ) );

		// Updates 0-based index to a 1-based index so the array key represents the new page number exactly (e.g. Page 1 vs Page 0).
		array_unshift( $page_numbers, false );
		unset( $page_numbers[0] );

		// Loop through the page numbers and assign fields to their new page number based on their old page number.
		foreach ( $page_numbers as $new_page_number => $old_page_number ) {

			// $pages is still a 0-based indexed array; subtract 2 from the old page number to find the right page.
			$page = rgar( $pages, $old_page_number - 2 );
			if ( ! $page ) {
				continue;
			}

			foreach ( $filtered_fields as $index => &$field ) {
				if ( $field->pageNumber == $old_page_number && $field->type !== 'page' ) {

					$field->pageNumber = $new_page_number;

					// Update the page to its new page number and inject it into the filtered fields ahead.
					$page->pageNumber = $new_page_number;
					array_splice( $filtered_fields, $index, 0, array( $page ) );

				}
			}
		}

		unset( $field );

		return $filtered_fields;
	}

	public function get_random_field_ids( $fields ) {

		if ( ! empty( $this->all_random_field_ids ) ) {
			return $this->all_random_field_ids;
		}

		$this->all_random_field_ids = array_map( 'intval', wp_list_pluck( $fields, 'id' ) );

		return $this->all_random_field_ids;
	}

	public function get_selected_field_ids( $entry_id = false, $form = array() ) {

		// check if class has already init fields
		if ( ! empty( $this->selected_field_ids ) ) {
			return $this->selected_field_ids;
		}

		$hash = $this->get_selected_field_ids_hash( $form );

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
		$keys      = array_rand( $this->get_random_field_ids( $form['fields'] ), $this->display_count );

		if ( ! is_array( $keys ) ) {
			$keys = array( $keys );
		}

		foreach ( $keys as $key ) {
			$field_ids[] = $this->all_random_field_ids[ $key ];
		}

		$this->selected_field_ids = $field_ids;

		return $field_ids;
	}

	public function get_selected_field_ids_hash( $form ) {
		return wp_hash( implode( '_', $this->get_random_field_ids( $form['fields'] ) ) );
	}

	public function save_selected_field_ids_meta( $entry, $form ) {
		if ( (int) $form['id'] === (int) $this->_form_id ) {
			$hash = $this->get_selected_field_ids_hash( $form );
			gform_add_meta( $entry['id'], "_gfrf_field_ids_{$hash}", $this->get_selected_field_ids( false, $form ) );
		}
	}

}
