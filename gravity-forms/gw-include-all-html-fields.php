<?php
/**
 * Gravity Wiz // Gravity Forms // Include ALL HTML Fields in All Fields Merge Tags Output By Default
 * https://gravitywiz.com/create-dynamic-post-excerpts-gravity-forms/
 *
 * This adds support for the :allowHtmlFields modifier. Whhen the modifer is added to the all fields merge tags
 * like so {all_fields:allowHtmlFields} it includes all HTML fields by default.
 */
add_action( 'gform_merge_tag_filter', function( $value, $tag, $modifiers, $field ) {
	if ( $field->type == 'html' && ( $tag != 'all_fields' || in_array( 'allowHtmlFields', explode( ',', $modifiers ) ) ) ) {
		$value = $field->content;
	}
	return $value;
}, 10, 4 );
