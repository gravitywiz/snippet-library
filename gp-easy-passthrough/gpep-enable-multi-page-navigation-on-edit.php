<?php
/**
 * Gravity Perks // Multi-page Navigation + Easy Passthrough // Make Pages Navigable When Editing Entry
 * http://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 * http://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * When repopulating an entry into a form for editing via Easy Passthrough and
 * [this snippet](https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/), make all pages navigable if
 * Multi-page Navigation is enabled for the given form and configured to allow navigating to any completed page.
 */
// Update "123" with your form ID.
add_filter( 'gform_pre_render_123', function( $form ) {

	if ( is_callable( 'gp_easy_passthrough' ) ) {
		$session         = gp_easy_passthrough()->session_manager();
		$update_entry_id = $session[ gp_easy_passthrough()->get_slug() . '_' . $form['id'] ];
		if ( $update_entry_id ) {
			$_POST['gw_page_progression'] = GFFormDisplay::get_max_page_number( $form );
		}
	}

	return $form;
} );
