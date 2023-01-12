<?php
/**
 * Gravity Perks // Page Transitions // Disable Hiding the Form Footer
 * https://gravitywiz.com/documentation/gppt_hide_footer/
 */
add_filter( 'gppt_hide_footer', '__return_false', 10, 2 );
