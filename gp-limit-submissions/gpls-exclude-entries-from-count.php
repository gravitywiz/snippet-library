<?php
/**
 * Gravity Perks // GP Limit Submissions // Exclude Entries from Count by Meta or Field Value
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 *
 * Exclude entries from a Limit Submissions feed's count when a specific entry meta
 * key/value condition is met. The excluded entries still exist, but no longer count
 * toward the configured submission limit.
 *
 * Instructions:
 * 1. Install this snippet with your preferred method.
 *    https://gravitywiz.com/documentation/managing-snippets/
 *
 * 2. Update the configuration below to match your form, feed, and meta condition.
 */
add_action( 'gpls_before_query', function( $rule_test ) {

	// Configuration
	$config = array(
		'form_id'    => 123, // Update '123' to your form ID.
		'feed_id'    => null, // Optional. Set to a feed ID to target a specific GPLS feed.
		'meta_key'   => 'your_meta_key',  // e.g. 'payment_status' or '5' to target field ID 5
		'meta_value' => 'your_meta_value',
	);

	if ( (int) $rule_test->form_id !== (int) $config['form_id'] ) {
		return;
	}

	if ( $config['feed_id'] && (int) $rule_test->feed_id !== (int) $config['feed_id'] ) {
		return;
	}

	global $wpdb;

	$rule_test->where[] = $wpdb->prepare(
		'e.id NOT IN (
			SELECT entry_id FROM ' . $wpdb->prefix . 'gf_entry_meta
			WHERE meta_key = %s AND meta_value = %s
		)',
		$config['meta_key'],
		$config['meta_value']
	);

} );
