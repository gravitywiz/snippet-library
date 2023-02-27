<?php
/**
 * Gravity Perks // Advanced Phone Field // Replace the space separator.
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * When the phone[nationalNumberFormatted] modifier is used, it seperates the phone number with a space.
 * This snippet allows you to change the space seperator.
 */
add_filter( 'gpapf_merge_tag_value', function( $text, $modifiers ) {
	if ( $modifiers['phone'] == 'nationalNumberFormatted' ) {
		// Update '-' to any character you want to use as the seperator.
		$text = str_replace( ' ', '-', $text );
	}
	return $text;
}, 10, 2 );
