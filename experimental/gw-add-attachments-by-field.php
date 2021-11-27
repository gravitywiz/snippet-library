<?php
/**
 * Gravity Wiz // Gravity Forms // Add Attachments by Field
 * https://gravitywiz.com/
 *
 * Gravity Forms provides an option to add all uploaded files as attachments but it does not provide the ability to
 * choose which fields to attach. This snippet allows you to attach a specific field's uploaded files to a specific
 * notification by name and field ID.
 */
add_filter( 'gform_notification', function( $notification, $form, $entry ) {

	$notification_name = 'Notification A';
	$upload_field_id   = 1;

	return gw_add_attachments_by_field( $notification, $form, $entry, $notification_name, $upload_field_id );
}, 10, 3 );

add_filter( 'gform_notification', function( $notification, $form, $entry ) {

	$notification_name = 'Notification B';
	$upload_field_id   = 2;

	return gw_add_attachments_by_field( $notification, $form, $entry, $notification_name, $upload_field_id );
}, 10, 3 );

function gw_add_attachments_by_field( $notification, $form, $entry, $notification_name, $upload_field_id ) {

	if ( $notification['name'] !== $notification_name ) {
		return $notification;
	}

	$notification['attachments'] = rgar( $notification, 'attachments', array() );
	$upload_root = RGFormsModel::get_upload_root();

	$field = GFAPI::get_field( $form, $upload_field_id );
	$url   = rgar( $entry, $field->id );

	if ( empty( $url ) ) {
		return $notification;
	}

	if ( $field->multipleFiles ) {
		$uploaded_files = json_decode( stripslashes( $url ), true );
		foreach ( $uploaded_files as $uploaded_file ) {
			$attachment = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $uploaded_file );
			$notification['attachments'][] = $attachment;
		}
	} else {
		$attachment = preg_replace( '|^(.*?)/gravity_forms/|', $upload_root, $url );
		$notification['attachments'][] = $attachment;
	}

	return $notification;
}
