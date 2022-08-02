/**
 * Gravity Perks // Nested Forms // Disable Automatically Loading Child Form HTML
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * We recommend installing this snippet with our free Custom Javascript plugin:
 * https://gravitywiz.com/gravity-forms-custom-javascript/
 */
window.gform.addFilter('gpnf_fetch_form_html_on_load', function () {
        return false;
});
