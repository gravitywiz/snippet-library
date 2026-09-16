<?php
/**
 * Gravity Perks // Popups // Allow Top-Window Navigation
 * https://gravitywiz.com/documentation/gravity-forms-popups/
 *
 * Allow user-clicked links in selected popup content to navigate the current page when the links
 * use target="_top" or target="_parent". Popups not selected below retain the default sandbox.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration in the snippet:
 *    - Set $enable_globally to true to enable top-window navigation for every popup.
 *    - Otherwise, add the popup feed IDs that should allow top-window navigation to $enabled_feed_ids.
 */
add_filter( 'gpp_popup_config', function( $config, $feed ) {
	/**
	 * Set to true to enable this for every popup.
	 */
	$enable_globally = false;

	/**
	 * Popup feed IDs that should allow top-window navigation.
	 *
	 * These are popup feed IDs, not Gravity Forms form IDs.
	 */
	$enabled_feed_ids = array(
		123,
		456,
	);

	$feed_id = (int) ( $feed['id'] ?? 0 );

	if ( ! $enable_globally && ! in_array( $feed_id, $enabled_feed_ids, true ) ) {
		return $config;
	}

	$permission = 'allow-top-navigation-by-user-activation';
	$sandbox    = preg_split(
		'/\s+/',
		trim( (string) ( $config['iframeSandbox'] ?? '' ) )
	) ?: array();

	if ( ! in_array( $permission, $sandbox, true ) ) {
		$sandbox[] = $permission;
	}

	$config['iframeSandbox'] = implode( ' ', array_filter( $sandbox ) );

	return $config;
}, 10, 2 );
