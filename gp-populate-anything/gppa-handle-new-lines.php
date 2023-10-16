<?php
/**
 * Gravity Perks // Populate Anything // Handle New Lines
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Replace the `\n` new line terminator with an actual new line in template values. This is particularly
 * useful in custom templates for Paragraph fields where you are outputting multiple object values.
 * 
 * Example:
 *
 * ID: {post:ID}\nAuthor: {post:post_author}\nDate: {post:post_date}
 */
add_filter( 'gppa_process_template_value', function( $value, $field ) {
	if ( ! is_array( $value ) ) {
		$value = str_replace( '\n', "\n", $value );
	}
	return $value;
}, 10, 2 );
