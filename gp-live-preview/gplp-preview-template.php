<?php
/**
 * Gravity Perks // Live Preview // Preview Template
 * https://gravitywiz.com/documentation/gravity-forms-live-preview/
 * 
 * The absolute path to the desired template file. 
 * For example, /app/public/wp-content/themes/twentytwenty/page.php.
 */
add_filter( 'gplp_preview_template', function( $template ) {

    return get_stylesheet_directory() . '/custom-template.php';

} );
