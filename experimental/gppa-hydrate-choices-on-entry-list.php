<?php
/**
 * Gravity Perks // Populate Anything // Hydrate form on Entry List page to include dynamic choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Installation: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * Limitations:
 *  - This snippet will not work with fields that have dynamically populated choices that are dependent on another
 *    value in the entry
 */
function gppa_hydrate_form_entry_details( $form ) {
	if ( ! class_exists( 'GP_Populate_Anything' ) ) {
		return $form;
	}

	if ( ! method_exists( 'GFForms', 'get_page' ) || GFForms::get_page() !== 'entry_list' ) {
		return $form;
	}

	remove_filter( 'gform_form_post_get_meta', 'gppa_hydrate_form_entry_details' );
	$form = gp_populate_anything()->hydrate_form( $form, array() );
	add_filter( 'gform_form_post_get_meta', 'gppa_hydrate_form_entry_details' );

	return $form;
}
add_filter( 'gform_form_post_get_meta', 'gppa_hydrate_form_entry_details' );
