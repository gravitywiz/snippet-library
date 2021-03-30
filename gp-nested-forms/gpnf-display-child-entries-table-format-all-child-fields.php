<?php
/**
 * Gravity Perks // GP Nested Forms // Display Table Format for All Fields
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * MODIFIED: Show *all* child fields.
 *
 * Plugin Name:  GP Nested Forms - Display Table Format for All Fields
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  This snippet displays the Child entries of all Nested Forms field on a form in a table format.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_action( 'init', function () {

	if ( !is_callable( 'gp_nested_forms' ) ) {
		return;
	}

	add_filter( 'gform_merge_tag_filter', function ( $value, $merge_tag, $modifiers, $field, $raw_value ) {

		if ( $field->type != 'form' || $value === false ) {
			return $value;
		}

		$nested_form = GFAPI::get_form( rgar( $field, 'gpnfForm' ) );

		// Adds support for :filter modifier on Nested Form field merge tags but not {all_fields}.
		$nested_field_ids = wp_list_pluck( $nested_form['fields'], 'id' );
		if ( function_exists( 'gw_all_fields_template' ) ) {
			$_modifiers = gw_all_fields_template()->parse_modifiers( $modifiers );
			if ( $_modifiers['filter'] ) {
				$nested_field_ids = is_array( $_modifiers['filter'] ) ? $_modifiers['filter'] : array( $_modifiers['filter'] );
			}
		}

		$template    = new GP_Template( gp_nested_forms() );
		$nested_form = GFAPI::get_form( rgar( $field, 'gpnfForm' ) );
		$args        = array(
			'template'         => 'nested-entries-detail',
			'field'            => $field,
			'nested_form'      => $nested_form,
			'modifiers'        => $modifiers,
			'nested_fields'    => gp_nested_forms()->get_fields_by_ids( $nested_field_ids, $nested_form ),
			'entries'          => gp_nested_forms()->get_entries( $raw_value ),
			'actions'          => array(),
			'nested_field_ids' => $nested_field_ids,
			'labels'           => array( 'view_entry' => '' )
		);

		$value = $template->parse_template(
			array(
				sprintf( '%s-%s-%s.php', $args['template'], $nested_form['id'], $field->id ),
				sprintf( '%s-%s.php', $args['template'], $nested_form['id'] ),
				sprintf( '%s.php', $args['template'] ),
			), true, false, $args
		);

		return $value;
	}, 12, 5 );

} );
