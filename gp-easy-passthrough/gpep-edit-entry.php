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
			// GPEP doesn't check for the user's last entry until the form is being rendered... but GPLS needs to know
			// if we're updating an entry during validation which happens beforehand. Let's do the same legwork GPEP
			// will do later to check for the last submitted entry when that option is active.
			if ( ! $update_entry_id ) {
				$feeds = gp_easy_passthrough()->get_active_feeds( $form_id );
				// If no results were found, return false.
				if ( ! empty( $feeds ) ) {
					foreach ( $feeds as $feed ) {
						$source_form_id = $feed['meta']['sourceForm'];
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
				}
			}
		}
		return isset( $update_entry_id ) && $update_entry_id ? $update_entry_id : false;
	}

}

// Configurations
new GPEP_Edit_Entry( array(
	'form_id'        => 123,   // Set this to the form ID
	'delete_partial' => false, // Set this to true to delete partial entries if enabled
) );
