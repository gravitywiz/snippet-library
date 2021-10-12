<?php
/**
 * Gravity Perks // Live Preview // Filter Preview Template Path
 * https://gravitywiz.com/documentation/gravity-forms-live-preview/
 *
 * Change the page template used to render the live preview.
 */
add_filter( 'gplp_preview_template', function( $template ) {
    return get_stylesheet_directory() . '/custom-template.php';
} );
