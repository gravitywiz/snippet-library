<?php
/**
 * Gravity Wiz // Gravity Forms // Include HTML Fields in {all_fields} Merge Tag By Default
 * https://gravitywiz.com/
 */
add_action( 'gform_merge_tag_filter', function( $value, $tag, $modifiers, $field ) {
	if ( $field->type == 'html' && $tag !== 'all_fields' ) {
	    $value = $field->content;
	}
	return $value;
}, 10, 4 );
