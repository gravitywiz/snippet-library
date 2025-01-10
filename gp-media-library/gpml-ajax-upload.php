<?php
/**
 * Gravity Perks // Media Library // Ajax Upload
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 *
 * Instruction Video: https://www.loom.com/share/bed55943a25c44f697530ed79d1d3066
 *
 * Upload images to the Media Library as they are uploaded via the Gravity Forms Multi-file Upload field.
 *
 * Plugin Name:  GP Media Library - Ajax Upload
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-media-library/
 * Description:  Upload images to the Media Library as they are uploaded via the Gravity Forms Multi-file Upload field.
 * Author:       Gravity Wiz
 * Version:      0.14
 * Author URI:   https://gravitywiz.com/
 */
class GPML_Ajax_Upload {

	private $_args = array();
	private $form_id;

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'default_entry_id' => 1,
		) );

		$this->form_id = rgar( $args, 'form_id' );

		add_action( 'init', array( $this, 'init' ), 11 );

	}

	public function init() {

		if ( ! is_callable( 'gp_media_library' ) ) {
			return;
		}

		add_action( 'gform_post_multifile_upload', array( $this, 'upload' ), 10, 5 );

		remove_action( 'gform_entry_post_save', array( gp_media_library(), 'maybe_upload_to_media_library' ) );
		add_filter( 'gform_entry_post_save', array( $this, 'update_entry_field_values' ), 10, 2 );

		// This filter ensures that this snippet is called on Gravity Flow inbox pages which do not
		// seem to trigger `gform_entry_post_save` when updating entries.
		add_filter( 'gravityflow_next_step', array( $this, 'gpml_gflow_next_step' ), 10, 4 );

	}

	public function is_applicable_form( $form ) {
		return empty( $this->form_id ) || (int) rgar( $form, 'id' ) === (int) $this->form_id;
	}

	public function gpml_gflow_next_step( $step, $current_step, $entry, $steps ) {
		$form = GFAPI::get_form( $entry['form_id'] );
		if ( ! $this->is_applicable_form( $form ) ) {
			return $step;
		}

		$this->update_entry_field_values( $entry, $form );
		return $step;
	}

	public function upload( $form, $field, $uploaded_filename, $tmp_file_name, $file_path ) {

		if ( ! gp_media_library()->is_applicable_field( $field ) || ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$field_uid = implode( '_', array( rgpost( 'gform_unique_id' ), $field->id ) );

		$renamed_file_path = str_replace( basename( $file_path ), $uploaded_filename, $file_path );
		rename( $file_path, $renamed_file_path );

		$id = gp_media_library()->upload_to_media_library( $renamed_file_path, $field, array( 'id' => $this->_args['default_entry_id'] ) );
		if ( is_wp_error( $id ) ) {
			return;
		}

		/**
		 * Fires after a file has been uploaded to the Media Library via AJAX.
		 *
		 * @param array               $id                Array of file IDs.
		 * @param GF_Field_FileUpload $field             The File Upload field object.
		 *
		 * @since 0.14
		 */
		do_action( 'gpmlau_after_upload', $id, $field );

		$key = sprintf( 'gpml_ids_%s', $field_uid );

		// Get file IDs.
		$ids = gform_get_meta( $this->_args['default_entry_id'], $key );

		if ( ! is_array( $ids ) ) {
			$ids = array();
		}

		$ids[] = $id[0];

		// Update file IDs.
		gform_update_meta( $this->_args['default_entry_id'], $key, $ids, $form['id'] );

		$output = array(
			'status' => 'ok',
			'data'   => array(
				'temp_filename'     => $tmp_file_name,
				'uploaded_filename' => str_replace( "\\'", "'", urldecode( $uploaded_filename ) ), //Decoding filename to prevent file name mismatch.
				'url'               => wp_get_attachment_image_url( $id[0] ),
			),
		);

		$output = json_encode( $output );

		die( $output );

	}

	public function update_entry_field_values( $entry, $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $entry;
		}

		foreach ( $form['fields'] as $field ) {

			if ( $field->get_input_type() != 'fileupload' || ! $field->multipleFiles ) {
				continue;
			}

			$field_uid = implode( '_', array( rgpost( 'gform_unique_id' ), $field->id ) );
			$ids       = gform_get_meta( $this->_args['default_entry_id'], sprintf( 'gpml_ids_%s', $field_uid ) );
			if ( empty( $ids ) ) {
				continue;
			}

			$new_value = array();

			foreach ( $ids as $id ) {
				if ( ! is_wp_error( $id ) ) {
					$new_value[] = wp_get_attachment_url( $id );
				}
			}
			// Append to current value if present
			$current_value = rgar( $entry, $field->id, false );
			if ( $current_value ) {
				$new_value = array_merge( json_decode( $current_value ), $new_value );
			}
			$entry[ $field->id ] = json_encode( $new_value );

			$entry[ $field->id ] = json_encode( $new_value );

			// Save our changes to the DB for this entry.
			GFAPI::update_entry_field( $entry['id'], $field->id, $entry[ $field->id ] );

			// Delete temporary file ID meta.
			gform_delete_meta( $this->_args['default_entry_id'], sprintf( 'gpml_ids_%s', $field_uid ) );

			// Save the file ID meta to the actual entry/field.
			gp_media_library()->update_file_ids( $entry['id'], $field->id, $ids );

		}

		return $entry;
	}

}

# Configuration

new GPML_Ajax_Upload();

# Apply to a specific Form.
// new GPML_Ajax_Upload( array(
// 	'form_id' => 292,
// ) );
