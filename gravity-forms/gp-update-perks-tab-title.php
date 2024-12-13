<?php
/**
 * Gravity Perks // Update Perks Tab Field Settings Title
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 */
add_action( 'gform_field_settings_tabs', function( $tabs ) {
	foreach ( $tabs as &$tab ) {
		if ( $tab['title'] === __( 'Perks', 'gravityperks' ) ) {
			// Update "Bonus Features" to whatever you want to call the perks tab.
			$tab['title'] = 'Bonus Features';
		}
	}
	return $tabs;
}, 99 );
