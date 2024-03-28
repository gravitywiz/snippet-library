<?php
/**
 * Gravity Perks // GP Google Sheets // Remove the settings tab.
 *
 * This snippet removes the GP Google Sheets settings tab from the GF Settings menu tabs.
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gform_settings_menu', function( $setting_tabs ) {

	/**
	 * IMPORTANT! Update these with roles/emails you want to exempt.
	 * The email can be full or from the '@' to the end.
	 *
	 * See {@link https://wordpress.org/documentation/article/roles-and-capabilities/ Roles}.
	 *
	 * e.g. 1. $roles = array( 'Administrator', 'Editor' );
	 *
	 *      2. Exempt 'ceo@ourdomain.com' and any email ending with '@ourotherdomain.com'.
	 *           $emails = array( 'ceo@ourdomain.com', '@ourotherdomain.com' );
	 */
	$roles  = array();
	$emails = array();
	$user   = wp_get_current_user();

	if ( ! empty( $roles ) ) {
		$roles      = array_map( 'strtolower', $roles );
		$user_roles = array_map( 'strtolower', (array) $user->roles );

		if ( count( array_intersect( $roles, $user_roles ) ) ) {
			return $setting_tabs;
		}
	}

	if ( ! empty( $emails ) ) {
		$regex = array_reduce( $emails, function( $str, $email ) {
			return $str . '|' . str_replace( '.', '\.', $email );
		}, '' );

		$regex = ltrim( $regex, '|' );
		$regex = "/($regex)$/i";

		if ( preg_match( $regex, $user->user_email ) ) {
			return $setting_tabs;
		}
	}

	return array_filter( $setting_tabs, function( $tab ) {
		return 'gp-google-sheets' !== $tab['name'];
	} );
} );
