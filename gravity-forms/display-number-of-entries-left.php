<?php
/**
 * Entries Left Shortcode
 * http://gravitywiz.com/2012/09/19/shortcode-display-number-of-entries-left/
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

	$form = RGFormsModel::get_form_meta( $id );
	if ( ! rgar( $form, 'limitEntries' ) || ! rgar( $form, 'limitEntriesCount' ) ) {
		return '';
	}

	$entry_count  = RGFormsModel::get_lead_count( $form['id'], '', null, null, null, null, 'active' );
	$entries_left = rgar( $form, 'limitEntriesCount' ) - $entry_count;
	$output       = $entries_left;

	if ( $format ) {
		$format = $format == 'decimal' ? '.' : ',';
		$output = number_format( $entries_left, 0, false, $format );
	}

	return $entries_left > 0 ? $output : 0;
}
