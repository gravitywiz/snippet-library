<?php
/**
 * Gravity Perks // Better User Activation // Conditional Activation Pages
 * https://gravitywiz.com/documentation/gravity-forms-better-user-activation/
 *
 * This experimental snippet allows you to load a different activation page depending on a value
 * in its associated entry.
 */
add_filter( 'gpbua_activation_page_id', function( $activation_page_id ) {

	// Update "1234" to the ID of your conditional activation page. Add additional page IDs as needed.
	$activation_page_ids = array( $activation_page_id, 1234 );

	// Allow conditional activation pages to be treated as activation pages in the post editor.
	if ( is_admin() ) {
		if ( in_array( rgget( 'post' ), $activation_page_ids ) ) {
			return (int) rgget( 'post' );
		} else if ( in_array( rgpost( 'post_ID' ), $activation_page_ids ) ) {
			return (int) rgpost( 'post_ID' );
		}
	}

	parse_str( $_SERVER['QUERY_STRING'], $query_args );
	$activation_key = rgar( $query_args, 'key', rgar( $query_args, 'gfur_activation' ) );
	if ( ! $activation_key ) {
		return $activation_page_id;
	}

	require_once( gf_user_registration()->get_base_path() . '/includes/signups.php' );
	global $wpdb;
	$wpdb->signups = $wpdb->base_prefix . 'signups';

	$signup = GFSignup::get( $activation_key );
	if ( is_wp_error( $signup ) ) {
		if ( $signup->get_error_code() !== 'already_active' ) {
			return $activation_page_id;
		}
		$meta = unserialize( $signup->error_data['already_active']->meta );
		$entry = GFAPI::get_entry( $meta['lead_id'] );
	} else {
		$entry = $signup->lead;
	}

	// Update "5" to the field ID for whose value the conditional will be based.
	$value = $entry[5];

	switch ( $value ) {
		// Update "my_custom_value" to the entry value that should trigger the conditional activation page.
		case 'my_custom_value':
			$activation_page_id = 3105;
			break;
	}

	return $activation_page_id;
} );
