<?php
/**
 * Gravity Perks // Nested Forms // Custom Parent Form Merge Tags
 * https://gravitywiz.com/documentation/gravity-forms-nested-form/
 *
 * Adds support to pass Parent form data to Child form on submission.
 * {parent_form}, {parent_field}, {parent_entry}
 * Example: {parent_form:title} will pass the Parent Form Title to the Child Form.
 *
 * Plugin Name:  GP Nested Forms — Custom Parent Form Merge Tags
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-form/
 * Description:  Adds support to pass Parent form data to Child form on submission.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_merge_tag_data', function( $data, $text, $form, $entry ) {

	$parent_entry_id = rgar( $entry, 'gpnf_entry_parent' );
	if ( $parent_entry_id ) {
		$parent_entry = GFAPI::get_entry( $parent_entry_id );
		if ( ! is_wp_error( $parent_entry ) ) {
			$data['parent_entry'] = $parent_entry;
		}
	}

	$parent_form_id = rgar( $entry, 'gpnf_entry_parent_form' );
	if ( $parent_form_id ) {
		$parent_form = GFAPI::get_form( $parent_form_id );
		if ( $parent_form ) {
			$data['parent_form']  = $parent_form;
			$data['parent_field'] = GFAPI::get_field( $parent_form, rgar( $entry, 'gpnf_entry_nested_form_field' ) );
		}
	}

	return $data;
}, 10, 4 );
