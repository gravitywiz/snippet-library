<?php
/**
 * Gravity Perks // Easy Passthrough // Edit Entry
 *
 * Edit entry ID specified in field with current form submission.
 *
 * @version   1.0
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/
 */
// Update "123" with your form ID.
add_filter( 'gform_entry_id_pre_save_lead_123', 'my_update_entry_on_form_submission', 10, 2 );
function my_update_entry_on_form_submission( $entry_id, $form ) {

	if ( is_callable( 'gp_easy_passthrough' ) ) {
		$session = gp_easy_passthrough()->session_manager();
		$update_entry_id = $session[ gp_easy_passthrough()->get_slug() . '_' . $form['id'] ];
	}

	return isset( $update_entry_id ) && $update_entry_id ? $update_entry_id : $entry_id;
}
