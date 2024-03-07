<?php
/**
 * Gravity Perks // Entry Blocks // Remove Copy Cat on Edit.
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Removes copy cat functionality on Entry Blocks' Edit Page.
 */
add_filter( 'gform_pre_render', 'gpeb_remove_gpcc_on_edit' );
add_filter( 'gform_pre_process', 'gpeb_remove_gpcc_on_edit' );

function gpeb_remove_gpcc_on_edit( $form ) {

	$is_block = (bool) rgpost( 'gpeb_entry_id' );
	if ( ! $is_block ) {
		$is_block = class_exists( 'WP_Block_Supports' ) && rgar( WP_Block_Supports::$block_to_render, 'blockName' ) === 'gp-entry-blocks/edit-form';
		if ( ! $is_block ) {
			return $form;
		}
	}

	foreach ( $form['fields'] as &$field ) {
		// remove any of the copy cat classes from the field.
		preg_match_all( '/copy-([0-9]+(?:.[0-9]+)*)-to-([0-9]+(?:.[0-9]+)*)(?:-if-([0-9]+(?:.[0-9]+)*))?/', $field['cssClass'], $matches, PREG_SET_ORDER );
		if ( empty( $matches ) ) {
			continue;
		}
		$field['cssClass'] = str_replace( $matches[0], '', $field['cssClass'] );
	}

	return $form;
}
