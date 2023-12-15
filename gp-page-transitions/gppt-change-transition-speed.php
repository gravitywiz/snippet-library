<?php
/**
 * Gravity Perks // Page Transitions // Change the Transition Speed
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 *
 * Change the page transition speed for a given form.
 */
// Update "123" to your form ID.
add_filter('gppt_scripts_args_123', function ( $args, $form ) {
    // Update "800" to your desired transition speed.
    $args['transitionSettings']['speed'] = 800;
    return $args;
}, 10, 2);
