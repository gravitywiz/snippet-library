/**
 * Gravity Perks // Reload Form // Instant Reload
 * https://gravitywiz.com/documentation/gravity-forms-reload-form/
 *
 * Instantly reload the form without any delay as soon as the confirmation is rendered.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
$( document ).bind( 'gform_confirmation_loaded', function( event, formId ) {
    $( '#gform_confirmation_wrapper_GFFORMID' ).parent().data( 'gwrf_GFFORMID' ).reloadForm();
} );
