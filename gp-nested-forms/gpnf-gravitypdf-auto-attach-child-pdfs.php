<?php
/**
 * Gravity Perks // Nested Forms + Gravity PDF // Auto-attach Child PDFs to Parent Form Notifications
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gform_notification', function ( $notification, $form, $entry ) {

	if ( ! class_exists( 'GPNF_Entry' ) || ! class_exists( 'GFPDF\Model\Model_PDF' ) ) {
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

			$pdf_model = GPDFAPI::get_mvc_class( 'Model_PDF' );
			$pdfs      = isset( $child_form['gfpdf_form_settings'] ) ? $pdf_model->get_active_pdfs( $child_form['gfpdf_form_settings'], $child_entry ) : array();

			foreach ( $pdfs as $pdf ) {
				$settings = $child_form['gfpdf_form_settings'][ $pdf['id'] ];

				// Generate our PDF.
				$filename = $pdf_model->generate_and_save_pdf( $child_entry, $settings );

				if ( ! is_wp_error( $filename ) ) {
					// Add PDF to notification PDFs.
					$attachments[] = $filename;
				}
			}
		}
	}

	return $notification;
}, 10, 3 );
