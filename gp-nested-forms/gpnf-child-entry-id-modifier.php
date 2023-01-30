<?php
/**
 * Gravity Perks // Nested Forms // Display Child Entry ID in Merge Tag Output
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions
 *
 * 1. Add the `:gpnf_child_entry_id` modifier to any {all_fields} or Nested Form field merge tag.
 *
 * Result
 *
 * ![Screenshot of {all_fields} and Nested Form field merge tag output with modifier applied.](https://gwiz.io/3DqJjYa)
 */
add_filter( 'gpnf_all_entries_nested_entry_markup', function( $markup, $field, $nested_form, $entry, $args ) {

	$modifiers = rgar( $args, 'modifiers' );
	if ( strpos( $modifiers, 'gpnf_child_entry_id' ) === false ) {
		return $markup;
	}

	$dom = new DOMDocument();
	$dom->loadHTML( $markup );

	$first_row  = $dom->getElementsByTagName( 'tr' )->item( 1 );
	$header_row = clone $first_row;
	$value_row  = clone $dom->getElementsByTagName( 'tr' )->item( 2 );

	$header_row->getElementsByTagName( 'strong' )->item( 0 )->nodeValue = 'Entry ID';
	$value_row->getElementsByTagName( 'font' )->item( 0 )->nodeValue    = $entry['id'];

	$table = $dom->getElementsByTagName( 'table' )->item( 1 );
	$table->insertBefore( $header_row, $first_row );
	$table->insertBefore( $value_row, $first_row );

	$markup = $dom->saveHTML();

	return $markup;
}, 10, 5 );
