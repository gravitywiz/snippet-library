<?php
/**
 * Gravity Perks // Easy Passthrough // Edit Entry
 * https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/
 *
 * Edit the entry that was passed through via GP Easy Passthrough rather than creating a new entry.
 *
 * Plugin Name:  GP Easy Passthrough — Edit Entry
 * Plugin URI:   https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/
 * Description:  Edit the entry that was passed through via GP Easy Passthrough rather than creating a new entry.
 * Author:       Gravity Wiz
 * Version:      1.3
 * Author URI:   https://gravitywiz.com/
 */
class GPEP_Edit_Entry {
	private $form_id;
	private $delete_partial;

	public function __construct( $options ) {

		$this->form_id        = rgar( $options, 'form_id' );
		$this->delete_partial = rgar( $options, 'delete_partial', true );

		add_filter( "gpep_form_{$this->form_id}", array( $this, 'capture_passed_through_entry_ids' ), 10, 3 );
		add_filter( "gform_entry_id_pre_save_lead_{$this->form_id}", array( $this, 'update_entry_id' ), 10, 2 );
		add_filter( "gpls_rule_groups_{$this->form_id}", array( $this, 'bypass_limit_submissions' ), 10, 2 );

		// Enable edit view in GP Inventory.
		add_filter( "gpi_is_edit_view_{$this->form_id}", '__return_true' );

	}

	public function capture_passed_through_entry_ids( $form, $values, $passed_through_entries ) {

		if ( empty( $passed_through_entries ) ) {
			return $form;
		}

		// Add hidden input to capture entry IDs passed through via GPEP.

		add_filter( "gform_form_tag_{$form['id']}", function( $form_tag, $form ) use ( $passed_through_entries ) {
			$entry_ids = implode( ',', wp_list_pluck( $passed_through_entries, 'entry_id' ) );
			$hash      = wp_hash( $entry_ids );
			$value     = sprintf( '%s|%s', $entry_ids, $hash );
			$input     = sprintf( '<input type="hidden" name="%s" value="%s">', $this->get_passed_through_entries_input_name( $form['id'] ), $value );
			$form_tag .= $input;
			return $form_tag;
		}, 10, 2 );

		return $form;
	}

	public function update_entry_id( $entry_id, $form ) {

		$update_entry_id = $this->get_edit_entry_id( $form['id'] );
		if ( $update_entry_id ) {
			if ( $this->delete_partial
				&& is_callable( array( 'GF_Partial_Entries', 'get_instance' ) )
				&& $entry_id !== null
				&& ! empty( GF_Partial_Entries::get_instance()->get_active_feeds( $form['id'] ) )
			) {
				GFAPI::delete_entry( $entry_id );
			}
			return $update_entry_id;
		}

		return $entry_id;
	}

	public function get_passed_through_entries_input_name( $form_id ) {
		return "gpepee_passed_through_entries_{$form_id}";
	}

	public function get_passed_through_entry_ids( $form_id ) {

		$posted_value = rgpost( $this->get_passed_through_entries_input_name( $form_id ) );
		if ( empty( $posted_value ) ) {
			return array();
		}

		list( $entry_ids, $hash ) = explode( '|', $posted_value );
		if ( $hash !== wp_hash( $entry_ids ) ) {
			return array();
		}

		$entry_ids = explode( ',', $entry_ids );

		return $entry_ids;
	}

	public function get_edit_entry_id( $form_id ) {

		$entry_ids = $this->get_passed_through_entry_ids( $form_id );
		$entry_id  = array_shift( $entry_ids );

		/**
		 * Filter the ID that will be used to fetch assign the entry to be edited.
		 *
		 * @since 1.3
		 *
		 * @param int|bool $edit_entry_id The ID of the entry to be edited.
		 * @param int      $form_id       The ID of the form that was submitted.
		 */
		return gf_apply_filters( array( 'gpepee_edit_entry_id', $form_id ), $entry_id, $form_id );
	}

	public function bypass_limit_submissions( $rule_groups, $form_id ) {

		// Bypass GPLS if we're updating an entry.
		if ( $this->get_edit_entry_id( $form_id ) ) {
			$rule_groups = array();
		}

		return $rule_groups;
	}

}

// Configurations
new GPEP_Edit_Entry( array(
	'form_id'        => 123,   // Set this to the form ID.
	'delete_partial' => false, // Set this to false if you wish to preserve partial entries after an edit is submitted.
) );
