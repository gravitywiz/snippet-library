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
 *   2. See usage instructions at the bottom of the file
 */
class GPDEC_GFPDF_Delayed_Deletion {
	public $deletion_queue = array();

	function __construct( $args ) {
		$this->_args = wp_parse_args( $args, array(
			'form_id' => false,
		) );

		add_action( 'init', array( $this, 'add_hooks' ), 16 ); // Wait for all add-ons
	}

	public function add_hooks() {
		if ( ! function_exists( 'gp_disable_entry_creation' ) ) {
			return;
		}

		add_filter( 'gpdec_should_delete_entry_' . $this->_args['form_id'], '__return_false' );
		add_action( 'gfpdf_post_generate_and_save_pdf_notification', array( $this, 'post_generate_and_save' ), 50, 4 );
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}

	public function post_generate_and_save( $form, $entry, $settings, $notifications ) {
		if ( $form['id'] != $this->_args['form_id'] ) {
			return;
		}

		$this->deletion_queue[] = $entry;
	}

	public function shutdown() {
		if ( empty( $this->deletion_queue ) ) {
			return;
		}

		foreach ( $this->deletion_queue as $entry ) {
			gp_disable_entry_creation()->delete_form_entry( $entry );
		}
	}
}

/*
 * Basic Usage
 *
 * Uncomment the lines below (remove the preceding // on each line) and adjust the form ID accordingly.
 * You may also duplicate the class instantiation if this is required for more than one form.
 */

//new GPDEC_GFPDF_Delayed_Deletion( array(
//	'form_id' => 3,
//) );
