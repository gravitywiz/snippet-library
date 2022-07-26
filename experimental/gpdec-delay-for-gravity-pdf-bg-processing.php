<?php
/**
 * Gravity Perks // Disable Entry Creation // Delay Deletion for Gravity PDF Background Processing
 * http://gravitywiz.com/documentation/gravity-forms-disable-entry-creation/
 *
 * Prevent the deletion of entries until PDFs are generated and attached to notifications. This is necessary if
 * using Gravity PDF's background processing as the entry will be deleted prior to the PDF being generated since
 * background processing uses subsequent requests rather than form submission to generate the PDF.
 *
 * Installation instructions:
 *   1. https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Update FORMID and $form_id accordingly.
 */
add_filter( 'gpdec_should_delete_entry_FORMID', '__return_false' );

add_action( 'gfpdf_post_generate_and_save_pdf_notification', function ( $form, $entry, $settings, $notifications ) {
	$form_id = 8;

	if ( ! function_exists( 'gp_disable_entry_creation' ) ) {
		return;
	}

	if ( $form['id'] !== $form_id ) {
		return;
	}

	gp_disable_entry_creation()->delete_form_entry( $entry );
}, 50, 4 );
