<?php
/**
 * Gravity Perks // Nested Forms // Double the default child entry max
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gpnf_child_entry_max', function( $child_entry_max ) { 
    return 200;
} );
