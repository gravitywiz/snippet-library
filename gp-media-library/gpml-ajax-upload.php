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
 * Version:      0.17
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

		// Validation has passed at this point and the files are already in the Media Library, so
		// discard them from the submission. This keeps GF from moving the temporary files into its
		// own uploads folder and saving those URLs to the entry alongside ours.
		add_action( 'gform_pre_submission', array( $this, 'discard_submission_files' ) );

		// Retain AJAX-uploaded files when using Save & Continue. Temporary files are purged from the
		// GF tmp folder long before a draft is typically resumed, so the files are re-injected into
		// the draft submission as populated file URLs pointing at the Media Library.
		add_filter( 'gform_incomplete_submission_pre_save', array( $this, 'retain_files_in_draft' ), 10, 3 );

	}

	public function is_applicable_form( $form ) {
		return empty( $this->form_id ) || (int) rgar( $form, 'id' ) === (int) $this->form_id;
	}

	public function is_applicable_field( $field ) {
		return $field->get_input_type() === 'fileupload' && $field->multipleFiles && gp_media_library()->is_applicable_field( $field );
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

		// Decoding filename to prevent file name mismatch.
		$decoded_filename = str_replace( "\\'", "'", urldecode( $uploaded_filename ) );

		/**
		 * Copy—rather than rename—the temporary file. Gravity Forms removes files from the submission
		 * when their temporary file is missing (see GF_Field_FileUpload::is_value_submission_empty()),
		 * which is why AJAX-uploaded files disappeared on validation errors and page navigation. The
		 * copy is what gets moved into the Media Library; GF's temporary file is left untouched.
		 */
		$tmp_dir       = trailingslashit( dirname( $file_path ) );
		$sideload_path = $tmp_dir . wp_unique_filename( $tmp_dir, $decoded_filename );

		if ( ! copy( $file_path, $sideload_path ) ) {
			return;
		}

		$id = gp_media_library()->upload_to_media_library( $sideload_path, $field, array( 'id' => $this->_args['default_entry_id'] ) );
		if ( is_wp_error( $id ) ) {
			if ( file_exists( $sideload_path ) ) {
				unlink( $sideload_path );
			}
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
				'uploaded_filename' => $decoded_filename,
				// GF 3.1.2+ rejects temporary files without a valid hash. Mirrors GFAsyncUpload::get_upload_hash().
				'hash'              => hash_hmac( 'sha256', $tmp_file_name . '|' . $decoded_filename, wp_salt( 'auth' ) ),
			),
		);

		$output = json_encode( $output );

		die( $output );

	}

	/**
	 * Remove our files from the submission once validation has passed.
	 *
	 * The files are already in the Media Library and update_entry_field_values() writes their URLs to
	 * the entry, so there is nothing left for GF to do with the temporary files.
	 */
	public function discard_submission_files( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$form_id  = absint( rgar( $form, 'id' ) );
		$tmp_path = rgar( GFFormsModel::get_tmp_upload_location( $form_id ), 'path' );

		foreach ( $form['fields'] as $field ) {

			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$field_uid = implode( '_', array( rgpost( 'gform_unique_id' ), $field->id ) );
			if ( ! gform_get_meta( $this->_args['default_entry_id'], sprintf( 'gpml_ids_%s', $field_uid ) ) ) {
				continue;
			}

			$input_name = 'input_' . absint( $field->id );
			$files      = rgars( GFFormsModel::$uploaded_files, $form_id . '/' . $input_name, array() );

			// Clean up the temporary files; they've served their purpose.
			if ( $tmp_path && is_array( $files ) ) {
				foreach ( $files as $file ) {
					$temp_filename = rgar( (array) $file, 'temp_filename' );
					if ( ! $temp_filename ) {
						continue;
					}
					$temp_filepath = $tmp_path . wp_basename( $temp_filename );
					if ( file_exists( $temp_filepath ) ) {
						unlink( $temp_filepath );
					}
				}
			}

			unset( GFFormsModel::$uploaded_files[ $form_id ][ $input_name ] );

			// GF 2.9.18+ caches the submission files on the field; clear that too.
			if ( method_exists( $field, 'set_submission_files' ) ) {
				$field->formId = $form_id;
				$field->set_submission_files( array(
					'existing' => array(),
					'new'      => array(),
				) );
			}
		}

	}

	public function retain_files_in_draft( $submission_json, $resume_token, $form ) {

		$submission = json_decode( $submission_json, true );
		$unique_id  = rgar( $submission, 'gform_unique_id' ) ?: rgpost( 'gform_unique_id' );
		if ( ! $this->is_applicable_form( $form ) || ! is_array( $submission ) || empty( $unique_id ) ) {
			return $submission_json;
		}

		foreach ( $form['fields'] as $field ) {
			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$ids = gform_get_meta( $this->_args['default_entry_id'], sprintf( 'gpml_ids_%s_%s', $unique_id, $field->id ) );
			if ( empty( $ids ) ) {
				continue;
			}

			$files = array();

			foreach ( $ids as $id ) {
				$url = is_wp_error( $id ) ? false : wp_get_attachment_url( $id );
				if ( ! $url ) {
					continue;
				}

				$files[] = $this->get_populated_file_url_details( $url, $field, $form );
			}

			if ( $files ) {
				$submission['files'][ 'input_' . $field->id ] = $files;
			}
		}

		return json_encode( $submission );
	}

	/**
	 * Build a file record that GF will accept on a resumed draft.
	 *
	 * GF 2.9.18+ requires a hash for files submitted as a URL (see
	 * GF_Field_FileUpload::is_valid_populated_file_url()); older versions ignore the extra key.
	 */
	public function get_populated_file_url_details( $url, $field, $form ) {

		$details = array(
			'uploaded_filename' => sanitize_file_name( wp_basename( $url ) ),
			'url'               => esc_url_raw( $url ),
			'id'                => GFFormsModel::get_uuid(),
		);

		// Mirrors GF_Field_FileUpload::get_populated_file_url_hash(); key order is significant.
		$hash_details = $details;

		$hash_details['field_id'] = (int) $field->id;
		$hash_details['form_id']  = (int) rgar( $form, 'id' );

		$details['hash'] = wp_hash( json_encode( $hash_details ) );

		return $details;
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
				$new_value = array_merge( (array) json_decode( $current_value ), $new_value );
			}

			// Guard against a file being saved by both GF and this snippet.
			$new_value = array_values( array_unique( $new_value ) );

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
