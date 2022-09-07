<?php
/**
 * Gravity Perks // Entry Blocks // Make Administrative Fields Visible on Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Make administrative fields visible when editing via Entry Blocks.
 */
add_filter( 'gform_pre_render', 'gpeb_set_field_visbility_on_edit' );
add_filter( 'gform_pre_process', 'gpeb_set_field_visbility_on_edit' );

function gpeb_set_field_visbility_on_edit( $form ) {

	$is_block = (bool) rgpost( 'gpeb_entry_id' );
	if ( ! $is_block ) {
		$is_block = class_exists( 'WP_Block_Supports' ) && rgar( WP_Block_Supports::$block_to_render, 'blockName' ) === 'gp-entry-blocks/edit-form';
		if ( ! $is_block ) {
			return $form;
		}
	}

	foreach ( $form['fields'] as &$field ) {
		if ( $field->visibility === 'administrative' ) {
			$field->visibility = 'visible';
		}
	}

	return $form;
}
