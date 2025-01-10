<?php
/**
 * Gravity Perks // Nested Forms + Gravity PDF // Auto-attach Child PDFs to Parent Form Notifications
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gform_notification', function ( $notification, $form, $entry ) {

	if ( ! class_exists( 'GPNF_Entry' ) || ! class_exists( 'GPDFAPI' ) ) {
		return $notification;
	}

	// Initialize attachments array.
	if ( ! isset( $notification['attachments'] ) ) {
		$notification['attachments'] = array();
	}

	$parent_entry = new GPNF_Entry( $entry );

	foreach ( $form['fields'] as $field ) {

		if ( $field->get_input_type() !== 'form' ) {
			continue;
		}

		$child_entries = $parent_entry->get_child_entries( $field->id );

		foreach ( $child_entries as $child_entry ) {
			$pdfs = GPDFAPI::get_entry_pdfs( $child_entry['id'] );

			if ( is_wp_error( $pdfs ) ) {
				continue;
			}

			foreach ( $pdfs as $pdf ) {
				// Generate PDF
				$pdf_path = GPDFAPI::create_pdf( $child_entry['id'], $pdf['id'] );

				if ( ! is_wp_error( $pdf_path ) ) {
					// Add PDF to notification PDFs.
					$notification['attachments'][] = $pdf_path;
				}
			}
		}
	}

	return $notification;
}, 10, 3 );
