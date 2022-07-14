<?php
/**
 * Gravity Perks // Page Transitions // Disable Hiding the Form Footer
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 */
add_filter( 'gppt_hide_footer', '__return_false', 10, 2 );
