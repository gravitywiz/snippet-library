<?php
/**
 * Gravity Perks // Live Preview // Filter Preview Template Path
 * https://gravityperks.com/
 *
 * Change the template used to render the Live Preview.
 */
add_filter( 'gplp_preview_template', function( $template ) {
    return get_stylesheet_directory() . '/custom-template.php';
} );
