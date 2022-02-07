<?php
/**
 * Gravity Wiz // Gravity Forms // Entry Count Shortcode
 *
 * Extends the [gravityforms] shortcode, providing a custom action to retrieve the total entry count and
 * also providing the ability to retrieve counts by entry status (i.e. 'trash', 'spam', 'unread', 'starred').
 *
 * @version  1.0
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/
 */
add_filter( 'gform_shortcode_entry_count', 'gwiz_entry_count_shortcode', 10, 2 );
function gwiz_entry_count_shortcode( $output, $atts ) {

	extract( shortcode_atts( array(
		'id'     => false,
		'status' => 'total', // accepts 'total', 'unread', 'starred', 'trash', 'spam'
		'format' => false, // should be 'comma', 'decimal'
	), $atts ) );

	$valid_statuses = array( 'total', 'unread', 'starred', 'trash', 'spam' );

	if ( ! $id || ! in_array( $status, $valid_statuses ) ) {
		return current_user_can( 'update_core' ) ? __( 'Invalid "id" (the form ID) or "status" (i.e. "total", "trash", etc.) parameter passed.' ) : '';
	}

	$counts = GFFormsModel::get_form_counts( $id );
	$output = rgar( $counts, $status );

	if ( $format ) {
		$format = $format == 'decimal' ? '.' : ',';
		$output = number_format( $output, 0, false, $format );
	}

	return $output;
}
