<?php
/**
 * Gravity Wiz // Gravity Forms // Search by Form ID
 * https://gravitywiz.com/
 *
 * Allows searching for forms by their ID number in the Form List view.
 * The search will match both form titles and numeric form IDs.
 */
add_filter( 'gform_form_list_forms', function( $forms, $search_query, $active, $sort_column, $sort_direction, $trash ) {
	// Only proceed if there's a search query and it looks like a number
	if ( empty( $search_query ) || ! is_numeric( $search_query ) ) {
		return $forms;
	}

	$search_id = absint( $search_query );

	// Check if a form with this ID exists but isn't already in results
	$form_ids = wp_list_pluck( $forms, 'id' );

	if ( ! in_array( $search_id, $form_ids ) ) {
		$form = GFAPI::get_form( $search_id );
		if ( ! empty( $form ) ) {
			// Convert to object with required properties for list table
			$form_obj = new stdClass();
			$form_obj->id = isset( $form['id'] ) ? $form['id'] : $form->id;
			$form_obj->title = isset( $form['title'] ) ? $form['title'] : $form->title;
			$form_obj->is_active = isset( $form['is_active'] ) ? $form['is_active'] : $form->is_active;
			$form_obj->entry_count = 0;
			$form_obj->view_count = 0;
			$forms[] = $form_obj;
		}
	}

	return $forms;
}, 10, 6 );
