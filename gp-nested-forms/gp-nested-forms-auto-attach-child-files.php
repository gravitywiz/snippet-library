<?php
/**
 * Gravity Perks // Nested Forms // Auto-attach Uploaded Files from Child to Parent Notifications
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gform_notification', function( $notification, $form, $entry ) {

	if( ! class_exists( 'GPNF_Entry' ) ) {
		return $notification;
	}

	$upload_fields = GFCommon::get_fields_by_type( $form, array( 'fileupload' ) );

	// If parent form has upload fields, rely on the notification's Attachments setting.
	if( ! empty( $upload_fields ) ) {
		if ( ! rgar( $notification, 'enableAttachments', false ) ) {
			return $notification;
		}
	}
	// Otherwise, rely on a manually defined array of notification IDs.
	else {
		$notification_ids = array( '5daaedb49dc32', '5dbce25cc21c2' );
		if ( ! in_array( $notification['id'], $notification_ids ) ) {
			return $notification;
		}
	}

	$attachments =& $notification['attachments'];
	$parent_entry = new GPNF_Entry( $entry );

	foreach( $form['fields'] as $field ) {

		if( $field->get_input_type() !== 'form' ) {
			continue;
		}

		$upload_root   = GFFormsModel::get_upload_root();
		$upload_fields = GFCommon::get_fields_by_type( GFAPI::get_form( $field->gpnfForm ), array( 'fileupload' ) );
		$child_entries = $parent_entry->get_child_entries( $field->id );

		foreach( $child_entries as $child_entry ) {
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
