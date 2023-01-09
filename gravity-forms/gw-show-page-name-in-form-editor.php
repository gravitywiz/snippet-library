<?php
/**
 * Gravity Wiz // Gravity Forms // Show Page Name in Form Editor
 *
 * Plugin Name: Gravity Wiz - Show Page Name in Form Editor
 * Description: If a page has a name, use it instead of `PAGE BREAK`.
 * Plugin URI:   http://gravitywiz.com/
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   http://gravitywiz.com
 *
 * Installation:
 *  * Install as a plugin or a snippet per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * Limitations:
 *  It does not replace the first page's name as it's not filterable.
 */
add_filter( 'gform_field_content', function ( $field_content, $field, $value, $entry_id, $form_id ) {
	if ( ! GFCommon::is_form_editor() ) {
		return $field_content;
	}

	if ( $field->type !== 'page' ) {
		return $field_content;
	}

	$form = GFAPI::get_form( $form_id );

	$page_index = array_search( $field->id, array_column( $form['fields'], 'id' ) );
	$page_name  = rgar( $form['pagination']['pages'], $page_index );

	if ( rgblank( $page_name ) ) {
		return $field_content;
	}

	return str_replace( 'PAGE BREAK', strtoupper( $page_name ), $field_content );
}, 10, 5 );
