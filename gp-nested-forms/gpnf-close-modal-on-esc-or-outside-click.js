/**
 * Gravity Perks // Nested Forms // Close Nested Form modal when ESC key is pressed or when clicked outside modal
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 * 2. Also add "Load Init Scripts Early" snippet to your theme's functions.php.
 *    https://github.com/gravitywiz/snippet-library/blob/master/experimental/gfjs-early-init-scripts.php
 */
window.gform.addFilter( 'gpnf_modal_args', function( args, formId, fieldId, gpnf ) {
    // Only run for parent form ID 1. Remove this if the behavior is desired for all forms.
    if ( formId != 1 ) {
        return args;
    }

    args.closeMethods = ['overlay', 'button', 'escape'];

    return args;
} );
