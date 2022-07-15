<?php
/**
 * Gravity Perks // Entry Blocks // Set Fields as Readonly On Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Configure fields to be readonly when editing via Entry Blocks by adding the "gpeb-readonly-on-edit" class to the
 * field's CSS Class Name field setting.
 */
add_filter( 'gform_pre_render', 'gpeb_set_readonly_on_edit' );
add_filter( 'gform_pre_process', 'gpeb_set_readonly_on_edit' );

function gpeb_set_readonly_on_edit( $form ) {

	$is_block = (bool) rgpost( 'gpeb_entry_id' );
	if ( ! $is_block ) {
		$is_block = class_exists( 'WP_Block_Supports' ) && rgar( WP_Block_Supports::$block_to_render, 'blockName' ) === 'gp-entry-blocks/edit-form';
	}

	if ( ! $is_block ) {
		return $form;
	}

	foreach ( $form['fields'] as &$field ) {
		if ( strpos( $field->cssClass, 'gpeb-readonly-on-edit' ) !== false ) {
			$field->gwreadonly_enable = true;
		}
	}

	return $form;
}
