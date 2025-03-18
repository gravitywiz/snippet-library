<?php
/**
 * Gravity Forms // Gravity Perks // Add to Block List Modifier
 * https://gravitywiz.com/documentation/gravity-forms-blocklist/
 *
 * This snippet introduces a `:blocklist` merge tag modifier for Gravity Forms.
 * When applied to any field merge tag, it generates a secure URL that allows 
 * users to easily add that field's value to WordPress's comment 
 * disallowed list (formerly the "comment blacklist").
 *
 * Tip: This can be used to allow users to prevent their email from being submitted
 * on any GPB-enabled Gravity Form.
 *
 * Usage:
 *
 * 1. Use the merge tag with the `:blocklist` modifier in your Gravity Forms notifications 
 *    or confirmations. For example:
 *    
 *    `{Email:1:blocklist}`
 *    
 *    This will output a secure URL that, when visited, adds the email address to the 
 *    WordPress blocklist.
 *
 * 2. Users clicking the generated link will automatically append the field's value to the 
 *    WordPress disallowed comment list.
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 *
 * 2. Use the `:blocklist` modifier on any field merge tag.
 */
add_filter( 'gform_merge_tag_filter', function ( $value, $merge_tag, $modifier, $field, $raw_value, $format ) {
	if ( $modifier !== 'blocklist' || empty( $raw_value ) || ! is_email( $raw_value ) ) {
		return $value;
	}

	$blocklist_url = add_query_arg([
		'value'   => $raw_value,
		'hash'    => wp_hash( $raw_value ),
	], site_url( '/?gf_blocklist=1' ));

	return esc_url( $blocklist_url );
}, 10, 6 );

add_action( 'init', function () {
	if ( empty( $_GET['gf_blocklist'] ) || $_GET['gf_blocklist'] !== '1' ) {
		return;
	}

	$value = rgget( 'value' );
	$hash  = rgget( 'hash' );
	if ( empty( $value ) || $hash !== wp_hash( $value ) ) {
		wp_die( 'Invalid value.' );
	}

	// Retrieve the current comment blocklist
	$blocklist = get_option( 'disallowed_keys', '' );

	// Append the new email if it's not already in the list
	if ( stripos( $blocklist, $value ) === false ) {
		$blocklist .= PHP_EOL . $value;
		update_option( 'disallowed_keys', trim( $blocklist ) );
	}

	wp_die( sprintf( '"%s" has been added to the blocklist.', $value ), 'Blocklist', ['response' => 200] );
});
