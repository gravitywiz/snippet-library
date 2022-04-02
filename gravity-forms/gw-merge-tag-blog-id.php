<?php
/**
 * Gravity Forms // Merge Tag: Blog ID
 * https://gravitywiz.com/s
 */
add_filter( 'gform_pre_replace_merge_tags', function( $text, $form, $lead, $url_encode, $esc_html, $nl2br, $format ) {
	if ( strpos( $text, '{blog_id}' ) !== false ) {
		$text = str_replace( '{blog_id}', get_current_blog_id(), $text );
	}
	return $text;
} );
