<?php
/**
 * Gravity Perks // Limit Dates // Disable Limit Dates on Gravity View Entry Edit
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 */
add_filter( 'gpld_has_limit_dates_enabled', function( $is_enabled ) {
	if ( is_callable( 'gravityview' ) && gravityview()->request->is_edit_entry() ) {
		$is_enabled = false;
	}
	return $is_enabled;
} );
