<?php
/**
 * Gravity Perks // Entry Blocks // Multi Field Sorting
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Use confirmation message from Form Settings when editing an entry in GPEB.
 * 
 * Instruction Video: https://www.loom.com/share/b9867d2735d44519bf563961e9b30bd2
 */
add_filter( 'gpeb_queryer_entries', function( $entries, $gf_queryer ) {

	// Update the form ID to match your form.
	if ( $gf_queryer->form_id != 933 ) {
		return $entries;
	}

	// Update the field IDs to match your form.
	$primary_sorting_field_id   = '1.6';
	$secondary_sorting_field_id = '1.3';
	$sorting_direction = 'ASC'; // 'ASC' or 'DESC'

	usort( $entries, function( $a, $b ) use (
		$primary_sorting_field_id,
		$secondary_sorting_field_id,
		$sorting_direction
	) {
		$a_primary = isset( $a[ $primary_sorting_field_id ] ) ? $a[ $primary_sorting_field_id ] : '';
		$b_primary = isset( $b[ $primary_sorting_field_id ] ) ? $b[ $primary_sorting_field_id ] : '';

		$cmp = strcasecmp( $a_primary, $b_primary );
		if ( $cmp === 0 ) {
			$a_secondary = isset( $a[ $secondary_sorting_field_id ] ) ? $a[ $secondary_sorting_field_id ] : '';
			$b_secondary = isset( $b[ $secondary_sorting_field_id ] ) ? $b[ $secondary_sorting_field_id ] : '';
			$cmp = strcasecmp( $a_secondary, $b_secondary );
		}

		return ( $sorting_direction === 'DESC' ) ? -$cmp : $cmp;
	});

	return $entries;
}, 10, 2 );
