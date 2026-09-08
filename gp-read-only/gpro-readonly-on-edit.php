<?php
/**
 * Gravity Perks // GP Read Only // Set Fields as Readonly On Edit
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 *
 * Configure fields to be readonly when editing via Gravity Forms Entries Edit, Entry Blocks, GravityView or Gravity Flow User Input Step.
 *
 * Usage:
 *
 * 1. Install this code as a plugin or as a snippet.
 * 2. Add the `gpro-readonly-on-edit` CSS Class Name to field's Custom CSS Class setting.
 * 3. Optionally, set GPRO_READONLY_GF_ENTRIES to true below to also make the fields
 *    readonly when editing an entry in the GF Entries edit. Defaults to false so
 *    administrators can still correct these values from the Entry Detail view.
 *
 * Plugin Name:  GP Read Only —  Set Fields as Readonly On Edit
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-read-only/
 * Description:  This snippet allows you to set read only for fields when editing.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   https://gravitywiz.com/
 */
// Set to true to also apply read-only when editing an entry in the GF Entries.
define( 'GPRO_READONLY_GF_ENTRIES', true );

add_filter( 'gform_admin_pre_render', 'gpeb_set_readonly_on_edit' );
add_filter( 'gform_pre_render', 'gpeb_set_readonly_on_edit' );
add_filter( 'gform_pre_process', 'gpeb_set_readonly_on_edit' );

function gpeb_set_readonly_on_edit( $form ) {

	$is_block = (bool) rgpost( 'gpeb_entry_id' );
	if ( ! $is_block ) {
		$is_block = class_exists( 'WP_Block_Supports' ) && rgar( WP_Block_Supports::$block_to_render, 'blockName' ) === 'gp-entry-blocks/edit-form';
	}

	$is_gravityview  = function_exists( 'gravityview' ) && gravityview()->request->is_edit_entry();
	$is_gravity_flow = rgget( 'lid' ) && rgget( 'page' ) == 'gravityflow-inbox';
	$is_entry_detail = false;
	if ( GPRO_READONLY_GF_ENTRIES ) {
		if ( class_exists( 'GFCommon' ) && GFCommon::is_entry_detail() ) {
			$is_entry_detail = true;
		} elseif ( is_admin() && rgget( 'page' ) == 'gf_entries' && rgget( 'view' ) == 'entry' && rgget( 'screen_mode' ) == 'edit' ) {
			$is_entry_detail = true;
		}
	}

	// disable the target field for GPEB, GravityView and Gravity Flow User Input step.
	if ( $is_block || $is_gravityview || $is_gravity_flow || $is_entry_detail ) {
		foreach ( $form['fields'] as &$field ) {
			if ( strpos( $field->cssClass, 'gpro-readonly-on-edit' ) !== false ) {
				$field->gwreadonly_enable = true;
			}
		}
	}

	return $form;
}

add_filter( 'gform_field_input', function( $input_html, $field ) {
	$is_entries_list_edit = is_admin() && rgget( 'page' ) == 'gf_entries' && rgget( 'view' ) == 'entry' && rgget( 'screen_mode' ) == 'edit';

	if ( GPRO_READONLY_GF_ENTRIES && ( ( class_exists( 'GFCommon' ) && GFCommon::is_entry_detail_edit() ) || $is_entries_list_edit ) && rgar( $field, 'gwreadonly_enable' ) ) {
		add_filter( 'gform_is_entry_detail', '__return_false' );
	}

	return $input_html;
}, 9, 2 );

add_filter( 'gform_field_input', function( $input_html ) {
	remove_filter( 'gform_is_entry_detail', '__return_false' );

	return $input_html;
}, 12 );
