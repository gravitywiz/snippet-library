<?php
/**
 * Gravity Wiz // Gravity Forms // Rename Uploaded Files
 *
 * Rename uploaded files for Gravity Forms. You can create a static naming template or using merge tags to base names on user input.
 *
 * Features:
 *  + supports single and multi-file upload fields
 *  + flexible naming template with support for static and dynamic values via GF merge tags
 *
 * Uses:
 *  + add a prefix or suffix to file uploads
 *  + include identifying submitted data in the file name like the user's first and last name
 *
 * @version   2.5
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/rename-uploaded-files-for-gravity-form/
 */
class GW_Rename_Uploaded_Files {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'          => false,
			'field_id'         => false,
			'template'         => '',
			'ignore_extension' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! is_callable( array( 'GFFormsModel', 'get_physical_file_path' ) ) ) {
			return;
		}

		add_filter( 'gform_entry_post_save', array( $this, 'rename_uploaded_files' ), 9, 2 );
		add_filter( 'gform_entry_post_save', array( $this, 'stash_uploaded_files' ), 99, 2 );

		add_action( 'gform_after_update_entry', array( $this, 'rename_uploaded_files_after_update' ), 9, 2 );
		add_action( 'gform_after_update_entry', array( $this, 'stash_uploaded_files_after_update' ), 99, 2 );

	}

	function rename_uploaded_files( $entry, $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $entry;
		}

		foreach ( $form['fields'] as &$field ) {

			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$uploaded_files = rgar( $entry, $field->id );

			if ( empty( $uploaded_files ) ) {
				continue;
			}

			$uploaded_files = $this->parse_files( $uploaded_files, $field );
			$stashed_files  = $this->parse_files( gform_get_meta( $entry['id'], 'gprf_stashed_files' ), $field );
			$renamed_files  = array();

			foreach ( $uploaded_files as $_file ) {

				// Don't rename the same files twice.
				if ( in_array( $_file, $stashed_files ) ) {
					$renamed_files[] = $_file;
					continue;
				}

				$dir  = wp_upload_dir();
				$dir  = $this->get_upload_dir( $form['id'] );
				$file = str_replace( $dir['url'], $dir['path'], $_file );

				if ( ! file_exists( $file ) ) {
					continue;
				}

				$renamed_file = $this->rename_file( $file, $entry );

				if ( ! is_dir( dirname( $renamed_file ) ) ) {
					wp_mkdir_p( dirname( $renamed_file ) );
				}

				$result = rename( $file, $renamed_file );

				$renamed_files[] = $this->get_url_by_path( $renamed_file, $form['id'] );

			}

			// In cases where 3rd party add-ons offload the image to a remote location, no images can be renamed.
			if ( empty( $renamed_files ) ) {
				continue;
			}

			if ( $field->get_input_type() == 'post_image' ) {
				$value = str_replace( $uploaded_files[0], $renamed_files[0], rgar( $entry, $field->id ) );
			} elseif ( $field->multipleFiles ) {
				$value = json_encode( $renamed_files );
			} else {
				$value = $renamed_files[0];
			}

			GFAPI::update_entry_field( $entry['id'], $field->id, $value );

			$entry[ $field->id ] = $value;

		}

		return $entry;
	}

	function get_upload_dir( $form_id ) {
		$dir         = GFFormsModel::get_file_upload_path( $form_id, 'PLACEHOLDER' );
		$dir['path'] = dirname( $dir['path'] );
		$dir['url']  = dirname( $dir['url'] );
		return $dir;
	}

	function rename_uploaded_files_after_update( $form, $entry_id ) {
		$entry = GFAPI::get_entry( $entry_id );
		$this->rename_uploaded_files( $entry, $form );
	}

	/**
	 * Stash the "final" version of the files after other add-ons have had a chance to interact with them.
	 *
	 * @param $entry
	 * @param $form
	 */
	function stash_uploaded_files( $entry, $form ) {

		foreach ( $form['fields'] as &$field ) {

			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$uploaded_files = rgar( $entry, $field->id );
			gform_update_meta( $entry['id'], 'gprf_stashed_files', $uploaded_files );

		}

		return $entry;
	}

	function stash_uploaded_files_after_update( $form, $entry_id ) {
		$entry = GFAPI::get_entry( $entry_id );
		$this->stash_uploaded_files( $entry, $form );
	}

	function rename_file( $file, $entry ) {

		$new_file = $this->get_template_value( $this->_args['template'], $file, $entry );
		$new_file = $this->increment_file( $new_file );

		return $new_file;
	}

	function increment_file( $file ) {

		$file_path = GFFormsModel::get_physical_file_path( $file );
		$pathinfo  = pathinfo( $file_path );
		$counter   = 1;

		if ( $this->_args['ignore_extension'] ) {
			while ( glob( str_replace( ".{$pathinfo['extension']}", '.*', $file_path ) ) ) {
				$file_path = str_replace( ".{$pathinfo['extension']}", "{$counter}.{$pathinfo['extension']}", GFFormsModel::get_physical_file_path( $file ) );
				$counter ++;
			}
		} else {
			// increment the filename if it already exists (i.e. balloons.jpg, balloons1.jpg, balloons2.jpg)
			while ( file_exists( $file_path ) ) {
				$file_path = str_replace( ".{$pathinfo['extension']}", "{$counter}.{$pathinfo['extension']}", GFFormsModel::get_physical_file_path( $file ) );
				$counter ++;
			}
		}

		$file = str_replace( basename( $file ), basename( $file_path ), $file );

		return $file;
	}

	function is_path( $filename ) {
		return strpos( $filename, '/' ) !== false;
	}

	function get_template_value( $template, $file, $entry ) {

		$info = pathinfo( $file );

		if ( strpos( $template, '/' ) === 0 ) {
			$dir      = wp_upload_dir();
			$template = $dir['basedir'] . $template;
		} else {
			$template = $info['dirname'] . '/' . $template;
		}

		// replace our custom "{filename}" psuedo-merge-tag
		$value = str_replace( '{filename}', $info['filename'], $template );

		// replace merge tags
		$form  = GFAPI::get_form( $entry['form_id'] );
		$value = GFCommon::replace_variables( $value, $form, $entry, false, true, false, 'text' );

		// make sure filename is "clean"
		$filename = $this->clean( basename( $value ) );
		$value    = str_replace( basename( $value ), $filename, $value );

		// append our file ext
		$value .= '.' . $info['extension'];

		return $value;
	}

	function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return $form_id == $this->_args['form_id'];
	}

	function is_applicable_field( $field ) {

		$is_file_upload_field   = in_array( GFFormsModel::get_input_type( $field ), array( 'fileupload', 'post_image' ) );
		$is_applicable_field_id = $this->_args['field_id'] ? $field['id'] == $this->_args['field_id'] : true;

		return $is_file_upload_field && $is_applicable_field_id;
	}

	function clean( $str ) {
		return sanitize_file_name( $str );
	}

	function get_url_by_path( $file, $form_id ) {

		$dir = $this->get_upload_dir( $form_id );
		$url = str_replace( $dir['path'], $dir['url'], $file );

		return $url;
	}

	function parse_files( $files, $field ) {

		if ( empty( $files ) ) {
			return array();
		}

		if ( $field->get_input_type() == 'post_image' ) {
			$file_bits = explode( '|:|', $files );
			$files     = array( $file_bits[0] );
		} elseif ( $field->multipleFiles ) {
			$files = json_decode( $files );
		} else {
			$files = array( $files );
		}

		return $files;
	}

}

# Configuration

new GW_Rename_Uploaded_Files( array(
	'form_id'          => 628,
	'field_id'         => 3,
	// most merge tags are supported, original file extension is preserved
	'template'         => '{Name (First):1.3}-{Name (Last):1.6}-{filename}',
	// Ignore extension when renaming files and keep them in sequence (e.g. a.jpg, a1.png, a2.pdf etc.)
	'ignore_extension' => false,
) );
