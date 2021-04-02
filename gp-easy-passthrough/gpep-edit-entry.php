<?php
/**
 * Gravity Perks // Easy Passthrough // Edit Entry
 *
 * Edit entry ID specified in field with current form submission.
 *
 * @version   1.1
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/
 */
class GPEPEditEntry {
	private $form_id;
	private $delete_partial;

	public function __construct( $options ) {
		$this->form_id        = rgar( $options, 'form_id' );
		$this->delete_partial = rgar( $options, 'delete_partial' );
		add_filter( sprintf( 'gform_entry_id_pre_save_lead_%s', $this->form_id ), array( $this, 'update_entry_id' ), 10, 2 );
	}

	public function update_entry_id( $entry_id, $form ) {
		if ( is_callable( 'gp_easy_passthrough' ) ) {
			$session         = gp_easy_passthrough()->session_manager();
			$update_entry_id = $session[ gp_easy_passthrough()->get_slug() . '_' . $form['id'] ];
			if ( $update_entry_id && $this->delete_partial ) {
				GFAPI::delete_entry( $entry_id );
			}
		}

		return isset( $update_entry_id ) && $update_entry_id ? $update_entry_id : $entry_id;
	}
}

// Configurations
new GPEPEditEntry( array(
	'form_id'        => 179,   // Set this to the form ID
	'delete_partial' => true, // Set this to true to delete partial entries if enabled
) );
