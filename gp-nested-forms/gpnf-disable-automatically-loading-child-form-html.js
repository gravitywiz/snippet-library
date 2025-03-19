/**
 * Gravity Perks // Nested Forms // Disable Automatically Loading Child Form HTML
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
window.gform.addFilter( 'gpnf_fetch_form_html_on_load', function() {
        return false;
} );
