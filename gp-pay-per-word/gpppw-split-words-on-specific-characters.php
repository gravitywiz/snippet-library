<?php
/**
 * Gravity Perks // Pay Per Word // Split Words on Specific Characters (PHP)
 * https://gravitywiz.com/documentation/gravity-forms-pay-per-word/
 */
add_filter( 'gpppw_word_count', function( $word_count, $words ) {
  // Splits words on periods, underscores and asterisks.
  $words = preg_replace( '/[\.\_\*]/', ' ', $words );
	return count( array_filter( preg_split( '/[ \n\r]+/', trim( $words ) ) ) );
}, 10, 2 );