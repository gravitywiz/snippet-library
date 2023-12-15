<?php
/**
 * Gravity Perks // QR Code // Decode HTML entities in QR codes that only consist of a URL
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * Instructions: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gpqr_content_pre_generate', function ( $content ) {
	// Only modify QR codes that only consist of a URL.
	if ( ! preg_match( '/^https?:\/\//', $content ) ) {
		return $content;
	}

	// Prevent infinite loop.
	if ( html_entity_decode( $content ) === $content ) {
		return $content;
	}

	// Use while loop in case there are recursively encoded HTML entities (e.g. &amp;amp;).
	while ( html_entity_decode( $content ) !== $content ) {
		$content = html_entity_decode( $content );
	}

	return $content;
} );
