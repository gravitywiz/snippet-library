<?php
/**
 * Gravity Perks // File Renamer // Defer Renaming Until Parent Form Submission
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * Defer file renaming on a GP Nested Forms' child form field until the parent form is submitted, so
 * a template that relies on the parent entry (e.g. a `{Parent:ID}` Unique ID) resolves correctly.
 *
 * Instructions
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of this snippet with your form and field IDs.
 */
class GPFR_Defer_Nested_Form_Rename {

	private $config;
	private $is_renaming = false;

	public function __construct( array $config ) {
		$this->config = wp_parse_args( $config, array(
			'parent_form_id'       => 0,
			'nested_form_field_id' => 0,
			'file_upload_field_id' => array(),
			'fields_to_backfill'   => array(),
		) );

		add_filter( 'gpfr_is_applicable_field', array( $this, 'is_applicable_field' ), 10, 3 );
		add_action( 'gform_entry_post_save', array( $this, 'rename_child_files' ), 50, 2 );
	}

	public function is_applicable_field( $is_applicable, $form, $field ) {
		if ( function_exists( 'gp_nested_forms' ) && gp_nested_forms()->is_nested_form_submission() ) {
			return false;
		}

		$file_field_ids = array_map( 'strval', (array) $this->config['file_upload_field_id'] );
		if ( $this->is_renaming && ! in_array( (string) $field->id, $file_field_ids, true ) ) {
			return false;
		}

		return $is_applicable;
	}

	public function rename_child_files( $parent_entry, $parent_form ) {
		if ( (int) rgar( $parent_form, 'id' ) !== (int) $this->config['parent_form_id'] || is_wp_error( $parent_entry )
			 || ! function_exists( 'gp_nested_forms' ) || ! function_exists( 'gp_file_renamer' ) ) {
			return $parent_entry;
		}

		$parent        = new GPNF_Entry( $parent_entry );
		$child_entries = $parent->get_child_entries( $this->config['nested_form_field_id'] );

		$this->is_renaming = true;

		foreach ( $child_entries as $child_entry ) {
			$child_form = GFAPI::get_form( $child_entry['form_id'] );
			if ( ! $child_form ) {
				continue;
			}

			$child_entry = $this->backfill_parent_merge_tags( $child_entry, $child_form, $parent_entry );
			$child_entry = gp_file_renamer()->rename_uploaded_files( $child_entry, $child_form );
			gp_file_renamer()->stash_renamed_file_urls( $child_entry, $child_form );
		}

		$this->is_renaming = false;

		return $parent_entry;
	}

	private function backfill_parent_merge_tags( $child_entry, $child_form, $parent_entry ) {
		foreach ( $this->config['fields_to_backfill'] as $field_id ) {
			$field         = GFFormsModel::get_field( $child_form, $field_id );
			$default_value = $field ? rgobj( $field, 'defaultValue' ) : '';
			if ( ! is_string( $default_value ) || strpos( $default_value, '{Parent:' ) === false ) {
				continue;
			}

			$value = preg_replace_callback( '/\{Parent:(\d+(?:\.\d+)?)\}/', function ( $matches ) use ( $parent_entry ) {
				return rgar( $parent_entry, $matches[1] );
			}, $default_value );

			if ( $value !== rgar( $child_entry, $field_id ) ) {
				GFAPI::update_entry_field( $child_entry['id'], $field_id, $value );
				$child_entry[ $field_id ] = $value;
			}
		}

		return $child_entry;
	}

}

# Configuration

new GPFR_Defer_Nested_Form_Rename( array(
	'parent_form_id'       => 123,        // ID of the parent form.
	'nested_form_field_id' => 4,          // ID of the Nested Form field on the parent form.
	'file_upload_field_id' => array( 5 ), // ID(s) of the File Upload field(s) on the child form to rename.
	'fields_to_backfill'   => array( 6 ), // ID(s) of the child field(s) with a {Parent:ID} value to populate.
) );
