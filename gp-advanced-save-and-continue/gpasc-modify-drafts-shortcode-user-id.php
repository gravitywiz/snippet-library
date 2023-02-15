<?php

/**
 * Gravity Perks // Advanced Save and Continue // Modify Drafts Shortcode User ID
 * https://gravitywiz.com/documentation/gravity-forms-advanced-save-and-continue/
 *
 * Modify the user_id attribute of the [gpasc_drafts] shortcode so that admins can view drafts for another user.
 *
 * ⚠ Note that this will only work if the current user is an admin and has permissions to view any Gravity Forms entry.
 */
add_filter( 'shortcode_atts_gpasc_drafts', function ( $out, $pairs, $atts, $shortcode ) {
	// change user_id to the id of the users whose drafts you want to display.
	$out['user_id'] = 1;
	return $out;
}, 10, 4 );
