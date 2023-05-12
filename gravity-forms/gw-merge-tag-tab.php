<?php
/**
 * Gravity Forms // Merge Tag: Add merge {tab} for tabbed space.
 * https://gravitywiz.com/
 */
add_filter( 'gform_pre_replace_merge_tags', function( $text, $form, $lead, $url_encode, $esc_html, $nl2br, $format ) {
	if ( strpos( $text, '{tab}' ) !== false ) {
		$text = str_replace( '{tab}', ' ', $text );
	}

	if ( strpos( $text, '{space}' ) !== false ) {
		$text = str_replace( '{space}', ' ', $text );
	}

	if ( strpos( $text, '{newline}' ) !== false ) {
		$text = str_replace( '{newline}', "\n", $text );
	}    
	return $text;
}, 10, 7 );
