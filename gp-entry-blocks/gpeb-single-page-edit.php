<?php
/**
 * Gravity Perks // Entry Blocks // Single Page Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Remove page breaks from Entry Blocks edit form.
 */
add_filter( 'gform_pre_render', function( $form ) {

	$is_block = (bool) rgpost( 'gpeb_entry_id' );
	if ( ! $is_block ) {
		$is_block = class_exists( 'WP_Block_Supports' ) && rgar( WP_Block_Supports::$block_to_render, 'blockName' ) === 'gp-entry-blocks/edit-form';
		if ( ! $is_block ) {
			return $form;
		}
	}

	$fields = array();

	foreach ( $form['fields'] as $field ) {
		if ( $field->type !== 'page' ) {
			$fields[] = $field;
		}
	}

	$form['fields'] = $fields;

	return $form;
} );
