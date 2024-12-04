<?php
/**
 * Gravity Perks // Nested Forms // Auto-attach Uploaded Files from Parent to Child Notifications
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This snippet automatically attaches files uploaded in the parent form to child form notifications.
 * If child forms have any File Upload fields, this snippet will attach files from specified parent upload fields
 * or all parent upload fields to notifications sent by the child form.
 *
 * Plugin Name:  GP Nested Forms - Auto-attach Uploaded Files from Parent to Child Notifications
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Auto-attach Uploaded Files from Parent to Child Notifications
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */

 class GPNF_Auto_Attach_Parent_Files {
	/**
	 * The ID of the child form to apply this to (optional).
	 */
	private $child_form_id = null;

	/**
	 * The IDs of the parent form fields containing files to be attached.
	 */
	private $parent_form_file_field_ids = array();

	/**
	 * The IDs of the child form notifications to which parent uploads should be attached (optional).
	 */
	private $child_notification_ids = array();

	function __construct( $config = array() ) {
		$this->set_config_data( $config );
		$this->init_hooks();
	}

	function init_hooks() {
		add_filter( 'gform_notification', array( $this, 'attach_parent_files_to_child_notifications' ), 10, 3 );
	}

	function set_config_data( $config ) {
		if ( ! empty( $config['child_form_id'] ) ) {
			$this->child_form_id = $config['child_form_id'];
		}

		if ( ! empty( $config['parent_form_file_field_ids'] ) ) {
			$this->parent_form_file_field_ids = $config['parent_form_file_field_ids'];
		}

		if ( ! empty( $config['child_notification_ids'] ) ) {
			$this->child_notification_ids = $config['child_notification_ids'];
		}
	}

	function attach_parent_files_to_child_notifications( $notification, $form, $entry ) {
		if ( ! class_exists( 'GPNF_Entry' ) ) {
			return $notification;
		}

		if ( $this->child_form_id && $form['id'] != $this->child_form_id ) {
			return $notification;
		}

		if ( ! $this->is_applicable_notification( $notification ) ) {
			return $notification;
		}

		$attachments =& $notification['attachments'];

		$parent_entry_id = rgar( $entry, 'gpnf_entry_parent' );
		if ( ! $parent_entry_id ) {
			return $notification;
		}

		$parent_entry = GFAPI::get_entry( $parent_entry_id );

		if ( ! $parent_entry ) {
			return $notification;
		}

		foreach ( $this->parent_form_file_field_ids as $field_id ) {
			$uploaded_files = rgar( $parent_entry, $field_id );

			if ( empty( $uploaded_files ) ) {
				continue;
			}

			$upload_root = GFFormsModel::get_upload_root();
			$upload_field = GFAPI::get_field( $parent_entry['form_id'], $field_id );
			$is_multiple_files = $upload_field->multipleFiles;

			$files = $is_multiple_files ? json_decode( $uploaded_files, true ) : [ $uploaded_files ];

			foreach ( $files as $file ) {
				$attachments[] = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $file );
			}
		}

		return $notification;
	}

	function is_applicable_notification( $notification ) {
		return empty( $this->child_notification_ids ) || in_array( $notification['id'], $this->child_notification_ids );
	}
}

/**
 * Configuration Examples
 *
 * Notes:
 * - Specify `child_form_id` to limit this to a specific child form.
 * - Specify `parent_form_file_field_ids` to limit which parent fields' files are attached.
 * - Specify `child_notification_ids` to limit this to specific child notifications.
 */

# Attach files from all parent file upload fields to all child notifications.
// new GPNF_Auto_Attach_Parent_Files();

# Attach files from parent file upload field with ID 5 to all child notifications.
new GPNF_Auto_Attach_Parent_Files( array(
	'parent_form_file_field_ids' => array( 2 ),
) );

# Attach files from parent file upload field with ID 5 to a specific child notification (ID '123abc').
// new GPNF_Auto_Attach_Parent_Files( array(
//	 'parent_form_file_field_ids' => array( 5 ),
//	 'child_notification_ids' => array( '123abc' ),
// ) );

# Attach files from all parent file upload fields to a specific child form (ID 10).
// new GPNF_Auto_Attach_Parent_Files( array(
//	 'child_form_id' => 10,
// ) );
