?php
/**
 * Gravity Perks // Nested Forms // Disable automatically loading child form HTML after child entries are added
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addFilter( 'gpnf_fetch_form_html_after_add', function () {
	return false;
} );
