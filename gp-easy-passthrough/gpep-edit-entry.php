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
		$this->delete_partial = rgar( $options, 'delete_partial', true );

		add_filter( "gform_entry_id_pre_save_lead_{$this->form_id}", array( $this, 'update_entry_id' ), 10, 2 );
		add_filter( "gpls_rule_groups_{$this->form_id}", array( $this, 'bypass_limit_submissions' ), 10, 2 );
		// Enable edit view in GP Inventory
		add_filter( "gpi_is_edit_view_{$this->form_id}", '__return_true' );
	}

	public function update_entry_id( $entry_id, $form ) {

		$update_entry_id = $this->get_update_entry_id( $form['id'] );
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

	public function bypass_limit_submissions( $rule_groups, $form_id ) {

		// Bypass GPLS if we're updating an entry.
		if ( $this->get_update_entry_id( $form_id ) ) {
			$rule_groups = array();
		}

		return $rule_groups;
	}

	public function get_update_entry_id( $target_form_id ) {

		if ( ! is_callable( 'gp_easy_passthrough' ) ) {
			return false;
		}

		$feeds = gp_easy_passthrough()->get_active_feeds( $target_form_id );
		if ( empty( $feeds ) ) {
			return false;
		}

		$session         = gp_easy_passthrough()->session_manager();
		$update_entry_id = false;

		foreach ( $feeds as $feed ) {
			// @todo evaluate conditional logic before processing feed...
			$source_form_id = $feed['meta']['sourceForm'];
			// GPEP doesn't check for the user's last entry until the form is being rendered... but GPLS needs to know
			// if we're updating an entry during validation which happens beforehand. Let's do the same legwork GPEP
			// will do later to check for the last submitted entry when that option is active.
			if ( rgars( $feed, 'meta/userPassthrough' ) && is_user_logged_in() ) {
				$last_submitted_entry = GFAPI::get_entries(
					$source_form_id,
					array(
						'field_filters' => array(
							array(
								'key'   => 'created_by',
								'value' => get_current_user_id(),
							),
						),
						'status'        => 'active',
					),
					array(
						'key'       => 'date_created',
						'direction' => 'DESC',
					),
					array( 'page_size' => 1 )
				);
				$update_entry_id      = rgars( $last_submitted_entry, '0/id', false );
			}
		}

		$has_token           = ! empty( rgget( 'ep_token' ) );
		$no_token_diff_forms = ! $has_token && (int) $target_form_id !== (int) $source_form_id;

		if ( ! $update_entry_id && ( $has_token || $no_token_diff_forms ) ) {
			$update_entry_id = isset( $session[ gp_easy_passthrough()->get_slug() . '_' . $target_form_id ] ) ? $session[ gp_easy_passthrough()->get_slug() . '_' . $target_form_id ] : false;
		}
		// Make sure entry is active before returning its ID for updating
		$entry = GFAPI::get_entry( $update_entry_id );
		if ( ! is_wp_error( $entry ) && $entry['status'] !== 'active' ) {
			$update_entry_id = false;
		}
		return $update_entry_id ? $update_entry_id : false;
	}

}

// Configurations
new GPEP_Edit_Entry( array(
	'form_id'        => 123,   // Set this to the form ID.
	'delete_partial' => false, // Set this to false if you wish to preserve partial entries after an edit is submitted.
) );
