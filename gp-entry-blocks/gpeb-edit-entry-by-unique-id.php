<?php
/**
 * Gravity Perks // Entry Blocks // Edit Entry by GP Unique ID
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Allow unauthenticated users to edit entries via a direct link using a GP Unique ID as an
 * access token. The unique ID in the URL (e.g., ?unique_id=abc123) grants edit access to the
 * matching entry without requiring the user to be logged in.
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
 * 3. Configure the form_id, field_id, and optionally the query_arg.
 */
class GPEB_Edit_Entry_By_Unique_ID {

	private $_args           = array();
	private $mapped_entry_id = null;
	private $mapped_entry    = null;
	private $did_lookup      = false;

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
		add_filter( 'gpeb_can_user_edit_entry', array( $this, 'require_matching_unique_id' ), 20, 3 );
		add_filter( 'gform_form_tag', array( $this, 'persist_unique_id_input' ), 20, 2 );
	}

	public function map_unique_id_to_edit_entry() {
		if ( ! $this->has_unique_id() ) {
			return;
		}

		$entry_id = $this->get_mapped_entry_id();
		if ( ! $entry_id ) {
			return;
		}

		$_GET['edit_entry']     = $entry_id;
		$_REQUEST['edit_entry'] = $entry_id;
	}

	public function limit_queryer_entries_to_mapped_entry( $entries, $queryer ) {
		if ( ! $this->is_edit_request() || (int) $queryer->form_id !== (int) $this->_args['form_id'] ) {
			return $entries;
		}

		$entry_id = $this->get_mapped_entry_id();
		if ( ! $entry_id ) {
			return array();
		}

		foreach ( $entries as $entry ) {
			if ( (int) rgar( $entry, 'id' ) === $entry_id ) {
				return array( $entry );
			}
		}

		if ( ! $this->mapped_entry || (int) rgar( $this->mapped_entry, 'id' ) !== $entry_id ) {
			return array();
		}

		return array( $this->mapped_entry );
	}

	public function require_matching_unique_id( $can_edit, $entry, $_current_user ) {
		if ( ! $this->is_edit_request() || ! is_array( $entry ) || empty( $entry['id'] ) ) {
			return $can_edit;
		}

		if ( (int) rgar( $entry, 'form_id' ) !== (int) $this->_args['form_id'] ) {
			return $can_edit;
		}

		$entry_id = $this->get_mapped_entry_id();

		return $entry_id && (int) $entry_id === (int) $entry['id'];
	}

	public function persist_unique_id_input( $form_tag ) {
		if ( ! $this->is_edit_request() ) {
			return $form_tag;
		}

		$unique_id = $this->get_unique_id();
		$key       = sanitize_key( $this->_args['query_arg'] );

		if ( $unique_id === '' || strpos( $form_tag, 'name="' . $key . '"' ) !== false ) {
			return $form_tag;
		}

		return $form_tag . sprintf(
			'<input type="hidden" name="%s" value="%s" />',
			esc_attr( $key ),
			esc_attr( $unique_id )
		);
	}

	private function get_mapped_entry_id() {
		if ( $this->mapped_entry_id !== null ) {
			return $this->mapped_entry_id;
		}

		$this->mapped_entry_id = $this->find_entry_id( $this->get_unique_id() );

		return $this->mapped_entry_id;
	}

	private function find_entry_id( $unique_id ) {
		if ( ! $unique_id || $this->did_lookup ) {
			return (int) $this->mapped_entry_id;
		}

		$this->did_lookup = true;

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
				'offset' => 0,
				'page_size' => 2,
			)
		);

		if ( is_wp_error( $entries ) || count( $entries ) !== 1 ) {
			return 0;
		}

		$this->mapped_entry = $entries[0];

		return (int) rgar( $this->mapped_entry, 'id' );
	}

	private function get_unique_id() {
		$key = sanitize_key( $this->_args['query_arg'] );
		$raw = $_POST[ $key ] ?? $_GET[ $key ] ?? '';

		return is_scalar( $raw ) ? trim( sanitize_text_field( wp_unslash( (string) $raw ) ) ) : '';
	}

	private function is_edit_request() {
		return ! empty( $_GET['edit_entry'] ) || ! empty( $_POST['gpeb_entry_id'] );
	}

	private function has_unique_id() {
		return '' !== $this->get_unique_id();
	}

	private function should_boot() {
		if ( wp_doing_cron() ) {
			return false;
		}

		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		$key = sanitize_key( $this->_args['query_arg'] );

		return isset( $_GET[ $key ] ) || isset( $_POST[ $key ] ) || isset( $_GET['edit_entry'] ) || isset( $_POST['gpeb_entry_id'] );
	}
}

# Configuration

new GPEB_Edit_Entry_By_Unique_ID( array(
	'form_id'   => 123, // Update "123" to your form ID.
	'field_id'  => 4, // Update "4" to ID of your Unique ID field.
	'query_arg' => 'unique_id', // Optional: change to customize the URL parameter name.
) );
