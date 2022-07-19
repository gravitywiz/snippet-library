<?php
/**
 * Gravity Perks // Word Count // Ignore Words Inside Parentheses
 * https://gravitywiz.com/documentation/gravity-forms-word-count/
 */
add_filter( 'gpwc_word_count', function( $word_count, $words ) {

	$words = preg_split( '/[ \n\r]+/', trim( preg_replace( '/\(([^)]+)\)/', '', $words ) ) );

	return count( $words );
}, 10, 2 );
