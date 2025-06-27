<?php
/**
 * Gravity Perks // Nested Forms // Auto-appprove Child Entries when its Parent Entry is approved with GravityView
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/a8ab5395a91d437eb1b393d7ba5b50c4
 *
 * Auto-appprove Child Entries when its Parent Entry is approved with GravityView
 */
add_action( 'gravityview/approve_entries/approved', function( $entry_id ) {

	$parent_entry   = GFAPI::get_entry( $entry_id );
	$parent_form    = GFAPI::get_form( $parent_entry['form_id'] );
	$nested_entries = array();

	foreach ( $parent_form['fields'] as $field ) {
		if ( $field instanceof GP_Field_Nested_Form ) {
			$_entries       = explode( ',', $parent_entry[ $field->id ] );
			$nested_entries = array_merge( $nested_entries, $_entries );
		}
	}

	foreach ( $nested_entries as $nested_entry_id ) {
		$nested_entry                = GFAPI::get_entry( $nested_entry_id );
		$nested_entry['is_approved'] = '1';
		GFAPI::update_entry( $nested_entry );
	}
}, 10, 1 );
