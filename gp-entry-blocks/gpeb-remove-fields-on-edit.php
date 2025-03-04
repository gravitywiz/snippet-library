<?php
/**
 * Gravity Perks // Entry Blocks // Remove Fields On Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Remove fields from the Entry Blocks edit form by automatically setting the field's visibility to "administrative".
 *
 * Instructions
 *
 * 1. Install the snippet:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Add the "gpeb-remove-on-edit" class to the Custom CSS Class field setting for any field you want to remove from
 *    the edit form.
 */
add_filter( 'gform_pre_render', 'gpeb_remove_fields_on_edit' );
add_filter( 'gform_pre_process', 'gpeb_remove_fields_on_edit' );

function gpeb_remove_fields_on_edit( $form ) {

	$is_block = (bool) rgpost( 'gpeb_entry_id' );
	if ( ! $is_block ) {
		$is_block = class_exists( 'WP_Block_Supports' ) && rgar( WP_Block_Supports::$block_to_render, 'blockName' ) === 'gp-entry-blocks/edit-form';
	}

	if ( ! $is_block ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {
		if ( strpos( $field->cssClass, 'gpeb-remove-on-edit' ) !== false ) {
			$field->visibility = 'administrative';
		}
	}

	return $form;
}
