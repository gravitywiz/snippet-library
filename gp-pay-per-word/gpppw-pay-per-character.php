<?php
/**
 * Gravity Perks // Pay Per Word // Surprise, Pay Per Character! (PHP)
 * https://gravitywiz.com/documentation/gravity-forms-pay-per-word/
 *
 * This snippet requires the JS counterpart gpppw-pay-per-character.js
 */
add_filter( 'gpppw_word_count', function( $word_count, $words ) {
	// Pay per character instead of words.
	$words = str_split( trim( $words ) );
	return count( $words );
}, 10, 2 );
