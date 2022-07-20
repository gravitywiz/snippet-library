<?php
/**
 * Gravity Perks // Live Preview // Set Custom Preview Template Path
 * https://gravitywiz.com/documentation/gravity-forms-live-preview/
 *
 * Set the path to a custom template with which your forms should be displayed when using Live Preview.
 */
add_filter( 'gplp_preview_template', function( $template ) {
	return get_stylesheet_directory() . '/custom-template.php';
} );
