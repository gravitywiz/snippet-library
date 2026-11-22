<?php
/**
 * Gravity Perks // Entry Blocks // Edit Entry by GP Unique ID
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Allow unauthenticated users to edit entries via a direct link using a GP Unique ID as an
 * access token. The unique ID in the URL (e.g., ?unique_id=abc123) grants edit access to the
 * matching entry without requiring the user to be logged in. Users who can already edit the
 * entry (e.g., admins or the entry's creator) can still edit it by its entry ID as usual.
 *
 * Usage: /your-page/?unique_id=abc123
 *
 * Instructions:
 *
 * 1. Install this snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/
 *
 * 2. Ensure GP Unique ID is installed and configured on the target form.
 *    https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * 3. Add an Entries block for the target form, with an Edit Entry block, to the page your links will point to.
 *    https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * 4. Configure the form_id, field_id, and optionally the query_arg.
 */
class GPEB_Edit_Entry_By_Unique_ID {

	private $_args           = array();
	private $mapped_entry_id = null;

	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args( $args, array(
			'form_id'   => 0,
			'field_id'  => 0,
			'query_arg' => 'unique_id',
		) );

		add_action( 'init', array( $this, 'init' ), 1 );
	}

	public function init() {
		if ( ! $this->_args['form_id'] || ! $this->_args['field_id'] || ! class_exists( 'GFAPI' ) ) {
			return;
		}

		if ( ! $this->should_boot() ) {
			return;
		}

		$this->map_unique_id_to_edit_entry();
		add_filter( 'gpeb_queryer_entries', array( $this, 'limit_queryer_entries_to_mapped_entry' ), 20, 2 );
		add_filter( 'gpeb_can_user_edit_entry', array( $this, 'allow_edit_with_matching_unique_id' ), 20, 2 );
		add_filter( 'gform_form_tag', array( $this, 'persist_unique_id_input' ), 20, 2 );
	}

	public function map_unique_id_to_edit_entry() {
		$entry_id = $this->get_mapped_entry_id();
		if ( ! $entry_id ) {
			return;
		}

		$_GET['edit_entry']     = $entry_id;
		$_REQUEST['edit_entry'] = $entry_id;
	}

	public function limit_queryer_entries_to_mapped_entry( $entries, $queryer ) {
		$entry_id = $this->get_mapped_entry_id();
		if ( ! $entry_id || (int) $queryer->form_id !== (int) $this->_args['form_id'] ) {
			return $entries;
		}

		// The block's own query is limited by its sorting and page size (an Edit Single block only queries its first
		// entry), so it rarely includes the mapped entry. Use the mapped entry instead, as long as it matches the
		// block's filters, which is the same check Entry Blocks applies to an entry opened by its entry ID. Fetch it
		// fresh, as the lookup ran before an edit submitted in this request was saved.
		$entry = GFAPI::get_entry( $entry_id );
		if ( is_wp_error( $entry ) || ! $queryer->entry_matches_filters( $entry ) ) {
			return array();
		}

		return array( $entry );
	}

	public function allow_edit_with_matching_unique_id( $can_edit, $entry ) {
		// Only ever grant access, so users who can already edit the entry keep that access without a unique ID.
		if ( $can_edit ) {
			return $can_edit;
		}

		$entry_id = $this->get_mapped_entry_id();

		return $entry_id && $entry_id === (int) rgar( $entry, 'id' );
	}

	public function persist_unique_id_input( $form_tag, $form ) {
		if ( ! $this->is_edit_request() || (int) rgar( $form, 'id' ) !== (int) $this->_args['form_id'] ) {
			return $form_tag;
		}

		$key = sanitize_key( $this->_args['query_arg'] );

		if ( strpos( $form_tag, 'name="' . $key . '"' ) !== false ) {
			return $form_tag;
		}

		return $form_tag . sprintf(
			'<input type="hidden" name="%s" value="%s" />',
			esc_attr( $key ),
			esc_attr( $this->get_unique_id() )
		);
	}

	private function get_mapped_entry_id() {
		if ( $this->mapped_entry_id === null ) {
			$this->mapped_entry_id = $this->find_entry_id( $this->get_unique_id() );
		}

		return $this->mapped_entry_id;
	}

	private function find_entry_id( $unique_id ) {
		if ( $unique_id === '' ) {
			return 0;
		}

		$entries = GFAPI::get_entries(
			(int) $this->_args['form_id'],
			array(
				'status'        => 'active',
				'field_filters' => array(
					array(
						'key'   => (string) $this->_args['field_id'],
						'value' => $unique_id,
					),
				),
			),
			null,
			array(
				'offset'    => 0,
				'page_size' => 2,
			)
		);

		if ( is_wp_error( $entries ) || count( $entries ) !== 1 ) {
			return 0;
		}

		return (int) rgar( $entries[0], 'id' );
	}

	private function get_unique_id() {
		$key = sanitize_key( $this->_args['query_arg'] );
		$raw = $_POST[ $key ] ?? $_GET[ $key ] ?? '';

		return is_scalar( $raw ) ? trim( sanitize_text_field( wp_unslash( (string) $raw ) ) ) : '';
	}

	private function is_edit_request() {
		return ! empty( $_GET['edit_entry'] ) || ! empty( $_POST['gpeb_entry_id'] );
	}

	private function should_boot() {
		if ( wp_doing_cron() ) {
			return false;
		}

		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		// Without a unique ID in the request, there is no access to grant.
		return $this->get_unique_id() !== '';
	}
}

# Configuration

new GPEB_Edit_Entry_By_Unique_ID( array(
	'form_id'   => 123, // Update "123" to your form ID.
	'field_id'  => 4, // Update "4" to ID of your Unique ID field.
	'query_arg' => 'unique_id', // Optional: change to customize the URL parameter name.
) );
