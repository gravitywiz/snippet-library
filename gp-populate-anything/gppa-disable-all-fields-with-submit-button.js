/**
 * Gravity Wiz // Gravity Forms Populate Anything // Disable All Fields
 *
 * Disable fields on all forms when a GPPA query is active and its submit button is disabled.
 *
 * @version 1.0
 * @author  Eihab Ibrahim <eihab@gravitywiz.com>
 * @license GPL-2.0+
 * @link    http://gravitywiz.com/
 */
(function ($) {
	// Form IDs to watch, this can be a list like: [ 25, 55, 144 ]
	const forms = [285];
	$(
		function () {
			// Find relevant submit button
			window.getSubmitButton = function () {
				for (let i = 0, max = forms.length; i < max; i++) {
					let form = forms[i];
					for (let gppaForm in window.gppaForms) {
						if (window.gppaForms.hasOwnProperty(gppaForm) && form === parseInt(gppaForm)) {
							const $form = $('input[name="is_submit_' + form + '"]').parents('form');
							return $form
								.find('.gform_footer, .gform_page_footer')
								.find('input[type="submit"], input[type="button"]');
						}
					}
				}
			};

			// Watch for disable filter
			window.gform.addFilter(
				'gppa_disable_form_navigation_toggling',
				function (disableStatus) {
					const $submitBtn = getSubmitButton();
					if ($submitBtn.prop('disabled') === false) {
						// Submit is about to be disabled, disable form fields
						document.querySelectorAll('.gfield input, .gfield select, .gfield textarea ').forEach(
							function (field) {
								if (!field.disabled) {  // Disable/track only active fields
									field.setAttribute('data-gppa-disabled', 'true');
									field.disabled = true;
								}
							}
						);
					} else {
						// Submit is about to be enabled, re-enable form fields
						document.querySelectorAll('[data-gppa-disabled="true"]').forEach(
							function (field) {
								field.setAttribute('data-gppa-disabled', 'false');
								field.disabled = false;
							}
						);
					}
					return disableStatus;
				}
			);
		}
	);
})(window.jQuery);