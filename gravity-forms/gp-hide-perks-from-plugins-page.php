<?php
/**
 * Gravity Perks // Hide Perks from Plugins Page
 * https://gravitywiz.com/documentation/
 *
 * Experimental Snippet 🧪
 *
 * So you've installed dozens of perks, each one more useful than the last, but you find that you'd rather not see them all
 * listed individually on your Plugins page. No problem! This snippet will hide perks from your Plugins page.
 *
 * Note: Perks will still show up on your Updates page.
 */
add_filter( 'all_plugins', function( $plugins ) {

	if ( ! is_callable( 'get_current_screen' ) || get_current_screen()->id !== 'plugins' ) {
		return $plugins;
	}

	$filtered_plugins = array();

	foreach ( $plugins as $slug => $plugin ) {
		if ( ! wp_validate_boolean( rgar( $plugin, 'Perk' ) ) ) {
			$filtered_plugins[ $slug ] = $plugin;
		}
	}

	return $filtered_plugins;
} );
