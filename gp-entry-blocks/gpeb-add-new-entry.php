<?php
/**
 * Gravity Perks // Entry Blocks // Add Entry Button
 * http://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/08a28f6e054c483780db738ac8f900bd
 * 
 * Instructions
 *
 * 1. Install the Manual Entries plugin.
 *    https://github.com/gravitywiz/snippet-library/blob/master/gravity-forms/gw-manual-entries.php
 *
 * 2. Install this snippet wherever you include PHP snippets.
 *
 * 3. Update the "page_id" parameter in the Configuration at the bottom of the snippet to match the ID of the page on
 *    which your Entry Blocks are displayed.
 *
 * 4. Add a custom "Add New Entry" link with a URL that follows this template:
 *    /my-entry-block-page/?add_new=1&id=123
 *
 *    a. Replace "my-entry-block-page" with the slug of the page on which your Entry Blocks are displayed.
 *    b. Replace "123" with the ID of the form you want to add an entry for.
 */
class GWME_Entry_Blocks_New_Entry {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'page_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( ! is_callable( 'gw_manual_entries' ) ) {
			return;
		}

		add_filter( 'wp', array( gw_manual_entries(), 'process_query_string' ) );

		add_filter( 'gfme_is_add_entry_request', array( $this, 'is_add_entry_request' ) );

		add_filter( 'gfme_edit_url', array( $this, 'set_edit_url'), 10, 3 );

	}

	public function is_add_entry_request( $is_add_entry_request ) {
		if ( rgget( 'add_new' ) && rgget( 'id' ) && get_queried_object_id() == $this->_args['page_id'] && current_user_can( 'administrator' ) ) {
			$is_add_entry_request = true;
		}
		return $is_add_entry_request;
	}

	public function set_edit_url( $edit_url, $form_id, $entry_id ) {
		if ( get_queried_object_id() == $this->_args['page_id'] ) {
			$edit_url = add_query_arg( array( 'edit_entry' => $entry_id ), get_permalink( $this->_args['page_id'] ) );
		}
		return $edit_url;
	}

}

# Configuration

new GWME_Entry_Blocks_New_Entry( array(
	'page_id' => 1234
) );
