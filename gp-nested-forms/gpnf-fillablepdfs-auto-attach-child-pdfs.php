<?php
/**
 * Gravity Perks // Nested Forms + FillablePDFs // Auto-attach Child PDFs to Parent Form Notifications
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gform_notification', function ( $notification, $form, $entry ) {

	if ( ! class_exists( 'GPNF_Entry' ) || ! is_callable( 'fg_fillablepdfs' ) ) {
		return $notification;
	}

	// Initialize attachments array.
	if ( ! isset( $notification['attachments'] ) ) {
		$notification['attachments'] = array();
	}

	$attachments  =& $notification['attachments'];
	$parent_entry = new GPNF_Entry( $entry );

	foreach ( $form['fields'] as $field ) {

		if ( $field->get_input_type() !== 'form' ) {
			continue;
		}

		$child_form    = GFAPI::get_form( $field->gpnfForm );
		$child_entries = $parent_entry->get_child_entries( $field->id );

		foreach ( $child_entries as $child_entry ) {

			// Get Fillable PDFs for entry.
			$entry_pdfs = gform_get_meta( $child_entry['id'], 'fillablepdfs' );

			// If no PDFs were found, return.
			if ( ! $entry_pdfs || empty( $entry_pdfs ) ) {
				return $notification;
			}

			// Loop through entry PDFs.
			foreach ( $entry_pdfs as $feed_id => $entry_pdf_id ) {

				// Get feed for entry PDF.
				$feed = fg_fillablepdfs()->get_feed( $feed_id );

				// If feed condition is not met, skip.
				if ( ! fg_fillablepdfs()->is_feed_condition_met( $feed, $child_form, $child_entry ) ) {
					continue;
				}

				// Get entry PDF.
				$entry_pdf = gform_get_meta( $child_entry['id'], 'fillablepdfs_' . $entry_pdf_id );

				// Add PDF to notification PDFs.
				$attachments[] = fg_fillablepdfs()->get_physical_file_path( $entry_pdf );

			}
		}
	}

	return $notification;
}, 10, 3 );
