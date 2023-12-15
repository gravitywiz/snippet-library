<?php
/**
 * Gravity Wiz // Gravity Forms // Replace Page Numbers with Letters
 * https://gravitywiz.com/
 */
add_filter( 'gform_progress_steps', function( $markup, $form ) {
	preg_match_all( '/<span class=\'gf_step_number.*?\'>(\d+)<\/span>/', $markup, $matches );
	foreach ( $matches[1] as $index => $value ) {
		$search  = $matches[0][ $index ];
		$replace = str_replace( $value, chr( 64 + $value ), $search );
		$markup  = str_replace( $search, $replace, $markup );
	}
	return $markup;
}, 10, 2 );
