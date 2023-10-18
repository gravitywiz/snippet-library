<?php
/**
 * Gravity Perks // Entry Blocks // Display Form Title and Description on Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 */
add_filter( 'gform_form_args', function( $form_args ) {

	$is_block = (bool) rgpost( 'gpeb_entry_id' );
	if ( ! $is_block ) {
		$is_block = class_exists( 'WP_Block_Supports' ) && rgar( WP_Block_Supports::$block_to_render, 'blockName' ) === 'gp-entry-blocks/edit-form';
		if ( ! $is_block ) {
			return $form_args;
		}
	}

	$form_args['display_title'] = true;
	$form_args['display_description'] = true;

	return $form_args;
} );
