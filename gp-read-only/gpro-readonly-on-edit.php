<?php
/**
 * Gravity Perks // GP Read Only // Set Fields as Readonly On Edit
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 *
 * Configure fields to be readonly when editing via Entry Blocks or GravityView or Gravity Flow User Input Step.
 *
 * Usage:
 *
 * 1. Install this code as a plugin or as a snippet.
 * 2. Add the `gpeb-readonly-on-edit` CSS Class Name to field's Custom CSS Class setting.
 *
 * Plugin Name:  GP Read Only —  Set Fields as Readonly On Edit
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-read-only/
 * Description:  This snippet allows you to set read only for fields when editing.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */

add_filter( 'gform_pre_render', 'gpeb_set_readonly_on_edit' );
add_filter( 'gform_pre_process', 'gpeb_set_readonly_on_edit' );

function gpeb_set_readonly_on_edit( $form ) {

$is_block = (bool) rgpost( 'gpeb_entry_id' );
if ( ! $is_block ) {
	$is_block = class_exists( 'WP_Block_Supports' ) && rgar( WP_Block_Supports::$block_to_render, 'blockName' ) === 'gp-entry-blocks/edit-form';
}

// disable the target field for GPEB, GravityView and Gravity Flow User Input step.
if ( $is_block || ( function_exists( 'gravityview' ) && gravityview()->request->is_edit_entry() ) || ( rgget( 'lid' ) && rgget( 'page' ) == 'gravityflow-inbox' ) ) {
	foreach ( $form['fields'] as &$field ) {
		if ( strpos( $field->cssClass, 'gpeb-readonly-on-edit' ) !== false ) {
			$field->gwreadonly_enable = true;
		}
	}
}

return $form;
}
