<?php
/**
 * Gravity Perks // Hide Perks from Plugins Page
 * https://gravitywiz.com/documentation/
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
