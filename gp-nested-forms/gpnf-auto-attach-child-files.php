<?php
/**
 * Gravity Perks // Nested Forms // Auto-attach Uploaded Files from Child to Parent Notifications
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This snippet automatically attaches files uploaded onto the child form to the parent form notifications.
 * If parent form has any File Upload fields, this snippet will rely on the "Attachments" setting for each parent notification.
 * Otherwise, you must specify a list of notifications by ID to which child uploads should be attached.
 *
 * Plugin Name:  GP Nested Forms - Auto-attach Uploaded Files from Child to Parent Notifications
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Auto-attach Uploaded Files from Child to Parent Notifications
 * Author:       Gravity Wiz
 * Version:      0.3
 * Author URI:   https://gravitywiz.com/
 */
class GPNF_Auto_Attach_Child_Files {
	/**
	 * The ID of the parent form to apply this to (optional).
	 */
	private $parent_form_id = null;

	/**
	 * The IDs of the child form field to apply this to (optional).
	 */
	private $child_form_field_ids = null;

	/**
	 * The IDs of the parent form notifications to which child uploads should be attached (optional).
	 */
	private $notification_ids = array();

	/**
	 * The IDs of child form fields whose files should be attached to notifications (optional).
	 */
	private $child_form_upload_field_ids = array();

	function __construct( $config = array() ) {
		$this->set_config_data( $config );
		$this->init_hooks();
	}

	function init_hooks() {
		add_filter( 'gform_notification', array( $this, 'handle_notification_attachments' ), 10, 3 );
	}

	function set_config_data( $config ) {
		if ( ! empty( $config['parent_form_id'] ) ) {
			$this->parent_form_id = $config['parent_form_id'];
		}

		if ( ! empty( $config['child_form_field_ids'] ) ) {
			$this->child_form_field_ids = $config['child_form_field_ids'];
		}

		if ( ! empty( $config['notification_ids'] ) ) {
			$this->notification_ids = $config['notification_ids'];
		}

		if ( ! empty( $config['child_form_upload_field_ids'] ) ) {
			$this->child_form_upload_field_ids = $config['child_form_upload_field_ids'];
		}
	}

	function handle_notification_attachments( $notification, $form, $entry ) {
		if ( ! class_exists( 'GPNF_Entry' ) ) {
			return $notification;
		}

		if ( $this->parent_form_id && $form['id'] != $this->parent_form_id ) {
			return $notification;
		}

		$upload_fields = GFCommon::get_fields_by_type( $form, array( 'fileupload', 'image_hopper', 'image_hopper_post' ) );

		if ( ! $this->is_applicable_notification( $notification ) ) {
			return $notification;
		}

		// If parent form has upload fields, rely on the notification's Attachments setting.
		if ( ! empty( $upload_fields ) && ! rgar( $notification, 'enableAttachments', false ) ) {
			return $notification;
		}

		$attachments  =& $notification['attachments'];
		$parent_entry = new GPNF_Entry( $entry );

		foreach ( $form['fields'] as $field ) {
			if ( $field->get_input_type() !== 'form' ) {
				continue;
			}

			if ( $this->child_form_field_ids && ! in_array( $field['id'], $this->child_form_field_ids ) ) {
				continue;
			}

			$child_form    = GFAPI::get_form( $field->gpnfForm );
			$upload_fields = GFCommon::get_fields_by_type( $child_form, array( 'fileupload', 'image_hopper', 'image_hopper_post' ) );
			$child_entries = $parent_entry->get_child_entries( $field->id );
			$upload_root   = GFFormsModel::get_upload_root();

			foreach ( $child_entries as $child_entry ) {
				foreach ( $upload_fields as $upload_field ) {
					if ( ! $this->is_applicable_upload_field( $upload_field ) ) {
						continue;
					}

					/*
					 * Handle GP Media Library as handle_attachments won't work as expected for the parent form since
					 * these file upload fields do not exist in the parent form, and it will fail to find the IDs.
					 */
					if ( function_exists( 'gp_media_library' ) ) {
						$gpml_file_ids = gp_media_library()->get_file_ids_by_entry( $child_entry, $child_form );

						/*
						 * gp_media_library()->get_file_ids_by_entry() returns a structure with array<fieldId, array<fileId>>
						 * so this is a cheap way to flatten it and avoid PHP errors at the same time.
						 */
						$gpml_ids = ( count( $gpml_file_ids ) > 0 ) ? call_user_func_array( 'array_merge', $gpml_file_ids ) : array();

						foreach ( $gpml_ids as $gpml_id ) {
							$attachments[] = get_attached_file( $gpml_id );
						}
					}

					$attachment_urls = rgar( $child_entry, $upload_field->id );
					if ( empty( $attachment_urls ) ) {
						continue;
					}

					$attachment_urls = $upload_field->multipleFiles ? json_decode( $attachment_urls, true ) : array( $attachment_urls );

					foreach ( $attachment_urls as $attachment_url ) {
						$attachment_url = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $attachment_url );
						$attachments[]  = $attachment_url;
					}
				}
			}
		}

		return $notification;
	}

	function is_applicable_notification( $notification ) {
		return empty( $this->notification_ids ) || in_array( $notification['id'], $this->notification_ids );
	}

	function is_applicable_upload_field( $upload_field ) {
		return empty( $this->child_form_upload_field_ids ) || in_array( $upload_field->id, $this->child_form_upload_field_ids );
	}
}

/**
 *  Notes:
 * - If notifications IDs are specified this snippet will only work for those specific forms.
 * - If child form upload field IDs are specified, only files from those fields will be attached.
 * - If the parent form has upload fields, they will be attached instead of the child form's fields.
 * - If none of these conditions are present, child form uploads will be attached by default.
 *
 * Configuration:
 *
 * 1) Ensure that the parent form's notification has the "Attach uploaded fields to notification" checked.
 * 2) [Optional] Modify one ore more of the following examples to limit the snippet to specific notification IDs or child form upload field IDs.
 *
 * 💡 Notification IDs can be retrieved from the nid parameter in the URL when editing a notification.
*/

# -------------------------------------------------
# Examples
# -------------------------------------------------

# Attach all child form uploads to all notifications.
// new GPNF_Auto_Attach_Child_Files();

# Attach all uploads from child forms to *only* the notification with id "63331f3fcf7f0".
// new GPNF_Auto_Attach_Child_Files( array(
// 	'notification_ids' => array( '63331f3fcf7f0' ),
// ) );

# Attach child form upload from field with ID 3 to all notifications.
// new GPNF_Auto_Attach_Child_Files( array(
// 	'child_form_upload_field_ids' => array( 3 ),
// ) );


# Attach child form upload from field with ID 3 to only notification with id "63331f3fcf7f0".
// new GPNF_Auto_Attach_Child_Files( array(
// 	'notification_ids'            => array( '63331f3fcf7f0' ),
// 	'child_form_upload_field_ids' => array( 3 ),
// ) );

# Attach all child form uploads only on the child form with field ID of 2 on the parent form with ID of 1
// new GPNF_Auto_Attach_Child_Files( array(
// 	'parent_form_id'              => 1,
// 	'child_form_field_ids'        => array( 2 ),
// ) );
