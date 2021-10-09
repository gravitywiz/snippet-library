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
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_notification', function( $notification, $form, $entry ) {
	// Notes:
	// - If notifications IDs are specified this snippet will only work for those specific forms.
	// - If the parent form has upload fields, they will be attached instead of the child form's fields.
	// - If neither of these conditions are present, child form uploads will be attached by default.
	// Configuration:
	// 1) Ensure that the parent form's notification has the "Attach uploaded fields to notification" checked.
	// 2) [Optional] Modify the following array to limit the snippet to specific notification IDs.
	// Notification IDs can be retrieved from the nid parameter in the URL when editing a notification.
	// Example: $notification_ids = array( '5daaedb49dc32', '5dbce25cc21c2' );
	$notification_ids = array();

	if ( ! class_exists( 'GPNF_Entry' ) ) {
		return $notification;
	}

	$upload_fields = GFCommon::get_fields_by_type( $form, array( 'fileupload' ) );

	if ( ! empty( $notification_ids ) && ! in_array( $notification['id'], $notification_ids ) ) {
		return $notification;
	} else {
		// If parent form has upload fields, rely on the notification's Attachments setting.
		if ( ! empty( $upload_fields ) ) {
			if ( ! rgar( $notification, 'enableAttachments', false ) ) {
				return $notification;
			}
		}
	}

	$attachments  =& $notification['attachments'];
	$parent_entry = new GPNF_Entry( $entry );

	foreach ( $form['fields'] as $field ) {

		if ( $field->get_input_type() !== 'form' ) {
			continue;
		}

		$upload_root   = GFFormsModel::get_upload_root();
		$upload_fields = GFCommon::get_fields_by_type( GFAPI::get_form( $field->gpnfForm ), array( 'fileupload' ) );
		$child_entries = $parent_entry->get_child_entries( $field->id );

		foreach ( $child_entries as $child_entry ) {
			foreach ( $upload_fields as $upload_field ) {

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
}, 10, 3 );
