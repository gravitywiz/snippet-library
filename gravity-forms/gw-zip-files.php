<?php
/**
 * Gravity Wiz // Gravity Forms // Zip Uploaded Files
 *
 * Create a zip file from all uploaded files and attach it to a notification.
 *
 * @todo
 *  - add support for multiple zip files per form (by field)
 *  - add support for removing file fields from {all_fields}
 *  - add support for linking to specific zip by 'zip_name' (i.e. {gwzip:zip_name}) or zip ID (i.e. gwzip:zipId})
 *  - add UI for naming zips
 *  - add UI for attaching zip to notification
 *
 * @version   1.2
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/
 */
class GW_Zip_Files {

	public function __construct( $args = array() ) {

		// make sure we're running the required minimum version of Gravity Forms
		if ( !property_exists( 'GFCommon', 'version' ) || !version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		// ZipArchive must be installed for PHP
		if ( !class_exists( 'ZipArchive' ) ) {
			return;
		}

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args(
			$args, array(
				'form_id'   => false,
				'field_ids' => false,
				'zip_name'  => 'gf-uploads'
			)
		);

		// time for hooks
		add_filter( 'gform_entry_meta', array( $this, 'register_entry_meta' ), 10, 2 );
		add_filter( 'gform_entries_field_value', array( $this, 'modify_zip_display_value' ), 10, 3 );

		add_action( 'gform_entry_created', array( $this, 'archive_files' ), 10, 2 );
		add_filter( 'gform_notification', array( $this, 'add_zip_as_attachment' ), 10, 3 );
		add_filter( 'gform_replace_merge_tags', array( $this, 'all_files_merge_tag' ), 10, 7 );
		add_filter( 'gform_replace_merge_tags', array( $this, 'zip_url_merge_tag' ), 10, 3 );

	}

	public function get_nested_entries( $entry, $parent_form ) {

		$entries = array();

		foreach ( $parent_form['fields'] as $field ) {
			if ( $field instanceof GP_Field_Nested_Form ) {
				$_entries = explode( ',', $entry[ $field->id ] );
				$entries = array_merge( $entries, $_entries );
			}
		}

		return array_unique( $entries );
	}

	public function archive_files( $entry, $form ) {

		if ( ! $this->is_applicable_form( $form ) || ! $this->has_applicable_field( $form ) ) {
			return;
		}

		$nested_entries = $this->get_nested_entries( $entry, $form );
		$nested_archive_files = array();

		if ( !empty( $nested_entries ) ) {
			foreach ( $nested_entries as $nested_entry_id ) {
				$nested_entry = GFAPI::get_entry( $nested_entry_id );
				if ( is_wp_error( $nested_entry ) ) {
					continue;
				}
				$nested_form = GFAPI::get_form( $nested_entry['form_id'] );
				$nested_archive_files = array_merge( $nested_archive_files, $this->get_entry_files( $nested_entry, $nested_form ) );
			}
		}

		$archive_files = $this->get_entry_files( $entry, $form );
		$archive_files = array_merge( $archive_files, $nested_archive_files );
		if ( empty( $archive_files ) ) {
			return;
		}

		$archive_file_paths = wp_list_pluck( $archive_files, 'path' );

		$zip = $this->create_zip( $archive_file_paths, $this->get_zip_paths( $entry, 'path' ) );
		if ( $zip ) {
			gform_update_meta( $entry['id'], $this->get_meta_key(), $this->get_zip_paths( $entry, 'url' ) );
		}

	}

	/**
	 * Get all files associated with an entry.
	 *
	 * @param $entry
	 * @param $form
	 *
	 * @return array $files An array of files, each "file" including the 'path' and 'url' grouped by field ID:
	 *
	 *     array(
	 *        FIELD_ID => array(
	 *            'path' => '/path/to/file.ext',
	 *            'url'  => 'http://url.com/path/to/file.ext'
	 *        ),
	 *        FIELD_ID => array(
	 *            'path' => '/path/to/file.ext',
	 *            'url'  => 'http://url.com/path/to/file.ext'
	 *        )
	 *     );
	 *
	 */
	public function get_entry_files( $entry, $form ) {

		$archive_files = array();

		foreach ( $form['fields'] as $field ) {

			if ( !$this->is_applicable_field( $field ) ) {
				continue;
			}

			$files = GFFormsModel::get_lead_field_value( $entry, $field );
			if ( $this->is_multi_file( $field ) ) {
				$files = json_decode( $files );
			}

			if ( empty( $files ) ) {
				continue;
			}

			if ( !is_array( $files ) ) {
				$files = array( $files );
			}

			$index = 1;

			foreach ( $files as $file ) {
				if ( $file_path = $this->convert_url_to_path( $file ) ) {

					$id = $field['id'];

					if ( $this->is_multi_file( $field ) ) {
						$id = "{$id}.{$index}";
						$index ++;
					}

					$archive_files[ $id ] = array(
						'path' => $file_path,
						'url'  => $file
					);

				}
			}

		}

		return $archive_files;
	}

	public function register_entry_meta( $entry_meta, $form_id ) {

		if ( ! empty( $this->_args['field_ids'] ) ) {
			return $entry_meta;
		}

		$entry_meta[$this->get_meta_key()] = array(
			'label'             => __( 'Form Uploads Archive', 'gravityforms' ),
			'is_numeric'        => false,
			'is_default_column' => false
		);

		return $entry_meta;
	}

	public function modify_zip_display_value( $value, $form_id, $field_id ) {

		if ( $this->is_applicable_form( $form_id ) ) {
			return $value;
		}

		if ( $field_id !== $this->get_meta_key() ) {
			return $value;
		}

		$value = sprintf( '<a href="%s">%s</a>', $value, __( 'Download Archive' ) );

		return $value;
	}

	public function is_applicable_form( $form ) {
		if ( isset( $_POST['gpnf_parent_form_id'] ) && !empty( $_POST['gpnf_parent_form_id'] ) ) {
			return !$this->_args['form_id'] || intval( $_POST['gpnf_parent_form_id'] ) == $this->_args['form_id'];
		}

		if ( is_int( $form ) ) {
			$form_id = $form;
		} else {
			$form_id = $form['id'];
		}

		return !$this->_args['form_id'] || $form_id == $this->_args['form_id'];
	}

	public function has_applicable_field( $form ) {

		foreach ( $form['fields'] as $field ) {
			if ( $this->is_applicable_field( $field ) ) {
				return true;
			}
		}

		return false;
	}

	public function is_applicable_field( $field ) {

		$input_type = GFFormsModel::get_input_type( $field );

		$is_applicable_input_type = in_array( $input_type, array( 'fileupload', 'post_image' ) );
		$is_applicable_field_id   = empty( $this->_args['field_ids'] ) || in_array( $field['id'], $this->_args['field_ids'] );

		return $is_applicable_input_type && $is_applicable_field_id;
	}

	public function is_multi_file( $field ) {
		return rgar( $field, 'multipleFiles' );
	}

	public function convert_url_to_path( $url ) {

		$bits = explode( '|:|', $url );
		$url  = array_shift( $bits );
		if ( !$url ) {
			return false;
		}

		if ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) {
			$path = preg_replace( '|^(.*?)/files/gravity_forms/|', BLOGUPLOADDIR . 'gravity_forms/', $url );
		} else {
			$path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $url );
		}

		return file_exists( $path ) ? $path : false;
	}

	public function create_zip( $files = array(), $destination = '', $overwrite = false ) {

		if ( !is_array( $files ) ) {
			return false;
		}

		if ( file_exists( $destination ) && !$overwrite ) {
			return false;
		}

		$valid_files = array();

		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				$valid_files[] = $file;
			}
		}

		if ( empty( $valid_files ) ) {
			return false;
		}

		$zip = new ZipArchive();

		if ( $zip->open( $destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE ) !== true ) {
			return false;
		}

		foreach ( $valid_files as $file ) {
			$zip->addFile( $file, basename( $file ) );
		}

		$zip->close();

		return file_exists( $destination ) ? $destination : false;
	}

	public function get_zip_paths( $entry, $type = false ) {

		$filename = $this->get_zip_filename( $entry['id'] );
		$paths    = GFFormsModel::get_file_upload_path( $entry['form_id'], $filename );

		foreach ( $paths as &$path ) {
			$path = str_replace( basename( $path ), $filename, $path );
		}

		return $type ? rgar( $paths, $type ) : $paths;
	}

	public function get_zip_filename( $entry_id ) {
		return $this->get_slug( $this->_args['zip_name'], $entry_id, $this->_args['field_ids'] ) . '.zip';
	}

	public function get_meta_key( $entry_id = false ) {
		return $this->get_slug( 'gw_zip', $entry_id, $this->_args['field_ids'] );
	}

	public function get_slug( $name, $entry_id = false, $field_ids = array() ) {

		$bits = array( $name );

		if ( $entry_id ) {
			$bits[] = $entry_id;
		}

		if ( !empty( $field_ids ) ) {
			$bits[] = md5( implode( $field_ids ) );
		}

		return implode( '_', $bits );
	}

	public function all_files_merge_tag( $text, $form, $entry ) {

		$search = '{all_files}';
		if ( strpos( $text, $search ) === false ) {
			return $text;
		}

		$replace = $this->get_all_files_output( $entry );

		return str_replace( $search, $replace, $text );
	}

	public function zip_url_merge_tag( $text, $form, $entry ) {

		$search = '{zip_url}';
		if ( strpos( $text, $search ) === false ) {
			return $text;
		}

		$zip_file = $this->get_zip_paths( $entry, 'path' );
		if ( ! $zip_file ) {
			return '';
		}

		$zip = new ZipArchive;
		if ( ! $zip->open( $zip_file ) || $zip->numFiles <= 0 ) {
			return '';
		}

		$replace = $this->get_zip_paths( $entry, 'url' );

		return str_replace( $search, $replace, $text );
	}

	public function get_all_files_output( $entry ) {

		$zip_file = $this->get_zip_paths( $entry, 'path' );
		if ( ! $zip_file ) {
			return '';
		}

		$zip = new ZipArchive;
		if ( ! $zip->open( $zip_file ) || $zip->numFiles <= 0 ) {
			return '';
		}

		$files      = $this->get_entry_files( $entry, GFAPI::get_form( $entry['form_id'] ) );
		$file_urls  = wp_list_pluck( $files, 'url' );
		$file_links = array();

		foreach ( $file_urls as $file_url ) {
			$file_links[] = sprintf( '<a href="%s">%s</a>', $file_url, basename( $file_url ) );
		}

		$replace = array_merge(
			array( __( 'Uploaded Files:' ) ),
			$file_links,
			array( sprintf( '<a href="%s">%s</a>', $this->get_zip_paths( $entry, 'url' ), __( 'All Files' ) ) )
		);

		$replace = implode( '', $replace );

		return $replace;
	}

	public function is_applicable_notification( $notification ) {
		if ( !isset( $this->_args['notifications'] ) ) {
			return true;
		}

		if ( isset( $this->_args["notifications"] ) && empty( $this->_args["notifications"] ) ) {
			return true;
		}

		if ( isset( $this->_args["notifications"] ) && !empty( $this->_args["notifications"] ) ) {
			return in_array( $notification['id'], $this->_args['notifications'] );
		}

		return true;
	}

	public function get_nested_entries_archives( $notification, $form, $entry ) {

		foreach ( $form['fields'] as $field ) {
			if ( $field instanceof GP_Field_Nested_Form ) {
				$value = RGFormsModel::get_lead_field_value( $entry, $field );

				if ( !empty( $value ) ) {
					foreach ( explode( ',', $value ) as $nested_entry_id ) {
						$nested_entry = GFAPI::get_entry( (int) $nested_entry_id );

						if ( $zip_path = $this->get_zip_paths( $nested_entry, 'path' ) ) {
							if ( isset( $notification['attachments'] ) ) {
								$notification['attachments'] = is_array( $notification['attachments'] ) ? $notification['attachments'] : array( $notification['attachments'] );
							} else {
								$notification['attachments'] = array();
							}

							$notification['attachments'][] = $zip_path;

						}
					}
				}
			}
		}

		return $notification;
	}

	public function add_zip_as_attachment( $notification, $form, $entry ) {
		if ( !$this->is_applicable_notification( $notification ) ) {
			return $notification;
		}

		if ( $this->is_applicable_form( $form ) && $zip_path = $this->get_zip_paths( $entry, 'path' ) ) {

			if ( isset( $notification['attachments'] ) ) {
				$notification['attachments'] = is_array( $notification['attachments'] ) ? $notification['attachments'] : array( $notification['attachments'] );
			} else {
				$notification['attachments'] = array();
			}

			$notification['attachments'][] = $zip_path;

		}

		return $notification;
	}

}

# Configuration

new GW_Zip_Files(
	array(
		'form_id' => 123,
		'zip_name' => 'my-sweet-archive',
		'notifications' => array( '5f4668ec2afbb' ),
	)
);