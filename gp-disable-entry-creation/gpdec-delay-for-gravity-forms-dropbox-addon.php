<?php

/**
 * Gravity Perks // Disable Entry Creation // Delay Deletion for Gravity Fomrs Dropbox Add-On
 * http://gravitywiz.com/documentation/gravity-forms-disable-entry-creation/
 *
 * Prevent the deletion of entries until dropbox feeds are processed to upload files to Dropbox folder.
 *
 * Installation instructions:
 *   1. https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. See usage instructions at the bottom of the file
 */
class GPDEC_GFDB_Delayed_Deletion {
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
		add_action( 'gform_dropbox_post_upload', array( $this, 'post_dropbox_upload' ), 10, 3 );
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}

	public function post_dropbox_upload( $feed, $entry, $form ) {error_log('aha');
		if ( $form['id'] != $this->_args['form_id'] ) {
			return;
		}

		$this->deletion_queue[] = $entry;
	}

	public function shutdown() {error_log('shutdown');

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

// new GPDEC_GFDB_Delayed_Deletion( array(
// 	'form_id' => 664,
// ) );
