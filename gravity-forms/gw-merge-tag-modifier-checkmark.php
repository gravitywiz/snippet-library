<?php
/**
 * Gravity Wiz // Gravity Forms // Display Checkmarks for Checkboxes
 * https://gravitywiz.com/
 *
 * Use the ":checkmark" modifier to display a checkmark for checkbox values. Will only display a checkmark if the
 * checkbox is checked. Can only be used on individual checkbox merge tags.
 *
 * Example: {Swimming:4.1:checkmark}
 */
add_filter( 'gform_merge_tag_filter', function( $value, $merge_tag, $modifier, $field_id ) {
	if ( ! rgblank( $value ) && $modifier === 'checkmark' ) {
		// If you want a fancier checkmark, you can use an SVG like so:
		// return '<svg style="height:1em;vertical-align:middle;" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20.285 2 9 13.567 3.714 8.556 0 12.272 9 21 24 5.715z" style="fill:green"/></svg>';
		return '&#10003;';
	}

	return $value;
}, 10, 4 );
