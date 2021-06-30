<?php
/**
 * Gravity Perks // Easy Passthrough // Edit Entry
 *
 * Edit entry ID specified in field with current form submission.
 *
 * @version   1.2
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/
 */
class GPEP_Edit_Entry {
	private $form_id;
	private $delete_partial;

	public function __construct( $options ) {

		$this->form_id        = rgar( $options, 'form_id' );
		$this->delete_partial = rgar( $options, 'delete_partial' );

		add_filter( "gform_entry_id_pre_save_lead_{$this->form_id}", array( $this, 'update_entry_id' ), 10, 2 );
		add_filter( "gpls_rule_groups_{$this->form_id}", array( $this, 'bypass_limit_submissions' ), 10, 2 );

	}

	public function update_entry_id( $entry_id, $form ) {

		$update_entry_id = $this->get_update_entry_id( $form['id'] );
		if ( $update_entry_id ) {
			if ( $this->delete_partial ) {
				GFAPI::delete_entry( $entry_id );
			}
			return $update_entry_id;
		}

		return $entry_id;
	}

	public function bypass_limit_submissions( $rule_groups, $form_id ) {

		// Bypass GPLS if we're updating an entry.
		if ( $this->get_update_entry_id( $form_id ) ) {
			$rule_groups = array();
		}

		return $rule_groups;
	}

	public function get_update_entry_id( $form_id ) {
		if ( is_callable( 'gp_easy_passthrough' ) ) {
			$session         = gp_easy_passthrough()->session_manager();
			$update_entry_id = $session[ gp_easy_passthrough()->get_slug() . '_' . $form_id ];
		}
		return isset( $update_entry_id ) && $update_entry_id ? $update_entry_id : false;
	}

}

// Configurations
new GPEP_Edit_Entry( array(
	'form_id'        => 123,   // Set this to the form ID
	'delete_partial' => false, // Set this to true to delete partial entries if enabled
) );
