<?php
/**
 * Gravity Forms // Entries Left Shortcode
 * https://gravitywiz.com/shortcode-display-number-of-entries-left/
 * 
 * Installation instructions: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gform_shortcode_entries_left', 'gwiz_entries_left_shortcode', 10, 2 );
function gwiz_entries_left_shortcode( $output, $atts ) {
	extract( shortcode_atts( array(
		'id'     => false,
		'format' => false // should be 'comma', 'decimal'
	), $atts ) );

	if ( ! $id ) {
		return '';
	}

	$form = GFAPI::get_form( $id );
	if ( ! rgar( $form, 'limitEntries' ) || ! rgar( $form, 'limitEntriesCount' ) ) {
		return '';
	}

	$entry_count  = GFAPI::count_entries( $form['id'], array( 
		'status' => 'active' 
	) );
	
	$entries_left = rgar( $form, 'limitEntriesCount' ) - $entry_count;
	$output       = $entries_left;

	if ( $format ) {
		$format = $format == 'decimal' ? '.' : ',';
		$output = number_format( $entries_left, 0, false, $format );
	}

	return $entries_left > 0 ? $output : 0;
}
