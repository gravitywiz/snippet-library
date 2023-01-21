<?php
/**
 * Gravity Perks // GP Nested Forms // Display Table Format for All Fields
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This snippet displays the child entries in a table format when using the {all_fields} merge tag with the gpnf_table modifier.
 * Add "gpnf_table" as a modifier to the {all_fields} merge tag to enable this functionality.
 * Add "gpnf_all_fields" as an additional modfier too show all the child fields when displaying in the table format.
 *
 * Examples:
 * Show Nested Form fields in the table format in the {all_fields} merge tag.
 * {all_fields:gpnf_table}
 * Show all child fields when displaying in the table format.
 * {all_fields:gpnf_table,gpnf_all_fields}
 *
 * Plugin Name:  GP Nested Forms - Display Table Format for All Fields
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  This snippet displays the child entries in a table format when using the {all_fields} merge tag with the gpnf_table modifier.
 * Author:       Gravity Wiz
 * Version:      0.3
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_merge_tag_filter', function ( $value, $merge_tag, $modifiers, $field, $raw_value ) {

	if ( ! is_callable( 'gp_nested_forms' ) || $field->type !== 'form' || $value === false || strpos( $modifiers, 'gpnf_table' ) === false ) {
		return $value;
	}

	$nested_form      = GFAPI::get_form( rgar( $field, 'gpnfForm' ) );
	$nested_field_ids = strpos( $modifiers, 'gpnf_all_fields' ) !== false ? wp_list_pluck( $nested_form['fields'], 'id' ) : $field->gpnfFields;

	// Adds support for All Field Template's :filter and :exclude modifiers.
	if ( function_exists( 'gw_all_fields_template' ) ) {
		$_modifiers = gw_all_fields_template()->parse_modifiers( $modifiers );
		if ( rgar( $_modifiers, 'filter' ) ) {
			$nested_field_ids = is_array( $_modifiers['filter'] ) ? $_modifiers['filter'] : array( $_modifiers['filter'] );
			if ( $merge_tag === 'all_fields' ) {
				$nested_field_ids = gpnf_parse_input_ids( $nested_field_ids, $field->id );
			}
		} elseif ( rgar( $_modifiers, 'exclude' ) ) {
			$excluded_field_ids = is_array( $_modifiers['exclude'] ) ? $_modifiers['exclude'] : array( $_modifiers['exclude'] );
			if ( $merge_tag === 'all_fields' ) {
				$excluded_field_ids = gpnf_parse_input_ids( $excluded_field_ids, $field->id );
			}
			$nested_field_ids = array_diff( $nested_field_ids, $excluded_field_ids );
		}
	}

	$excluded_field_types = array( 'html', 'section', 'password', 'captcha' );
	$all_nested_fields    = gp_nested_forms()->get_fields_by_ids( $nested_field_ids, $nested_form, true );

	$filtered_nested_fields = array();
	foreach ( $all_nested_fields as $nested_field ) {
		if ( ! in_array( $nested_field->type, $excluded_field_types, true ) ) {
			$filtered_nested_fields[] = $nested_field;
		}
	}

	$template    = new GP_Template( gp_nested_forms() );
	$nested_form = GFAPI::get_form( rgar( $field, 'gpnfForm' ) );
	$args        = array(
		'template'         => 'nested-entries-detail-simple',
		'field'            => $field,
		'nested_form'      => GFAPI::get_form( rgar( $field, 'gpnfForm' ) ),
		'modifiers'        => $modifiers,
		'nested_fields'    => $filtered_nested_fields,
		'entries'          => gp_nested_forms()->get_entries( $raw_value ),
		'actions'          => array(),
		'nested_field_ids' => $nested_field_ids,
		'labels'           => array( 'view_entry' => '' ),
	);

	$value = $template->parse_template(
		array(
			sprintf( '%s-%s-%s.php', $args['template'], $field->formId, $field->id ),
			sprintf( '%s-%s.php', $args['template'], $field->formId ),
			sprintf( '%s.php', $args['template'] ),
		), true, false, $args
	);

	return $value;
}, 12, 5 );

function gpnf_parse_input_ids( $input_ids, $nested_form_field_id ) {
	foreach ( $input_ids as &$input_id ) {
		if ( (int) $input_id === (int) $nested_form_field_id ) {
			$bits     = explode( '.', $input_id );
			$input_id = array_pop( $bits );
		}
	}
	return $input_ids;
}
