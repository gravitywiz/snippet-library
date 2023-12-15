<?php
/**
 * Gravity Wiz // Gravity Wiz // Email Groups
 * https://gravitywiz.com/
 *
 * Send notifications to a group of predefined emails with the power of merge tags! Define your email groups below and
 * then use the `{emailgroup}` merge tag to send to a specific group (e.g. `{emailgroup:group1}`).
 *
 * Screenshot: https://gwiz.io/3wvw2uj
 */
add_action( 'gform_merge_tag_data', function( $data ) {
	$data['emailgroup'] = array(
		'group1' => 'dave@gwiz.dev,clay@gwiz.dev',
		'group2' => 'dana@smiff.com,layla@smiff.com,summer@smiff.com,abram@smiff.com',
	);
	return $data;
} );
