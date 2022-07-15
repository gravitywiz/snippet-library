<?php
/**
 * Gravity Perks // Entry Blocks // Hide Product Fields on Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 */
add_filter( 'gform_pre_render', function( $form ) {
	if ( ! class_exists( 'WP_Block_Supports' ) || rgar( WP_Block_Supports::$block_to_render, 'blockName' ) !== 'gp-entry-blocks/edit-form' ) {
		return $form;
	}
	$filtered_fields = array();
	foreach ( $form['fields'] as &$field ) {
		if ( ! GFCommon::is_product_field( $field->type ) ) {
			$filtered_fields[] = $field;
		}
	}
	$form['fields'] = $filtered_fields;
	return $form;
} );
