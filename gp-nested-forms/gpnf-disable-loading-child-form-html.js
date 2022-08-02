/**
 * Gravity Perks // Nested Forms // Disable automatically loading child form HTML after child entries are deleted
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * We recommend installing this snippet with our free Custom Javascript plugin:
 * https://gravitywiz.com/gravity-forms-custom-javascript/
 */
 window.gform.addFilter('gpnf_fetch_form_html_after_delete', function () {
	return false;
});
